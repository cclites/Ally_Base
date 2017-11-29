<?php
namespace App\Http\Controllers\Api\Telefony;

use App\Activity;
use App\Exceptions\TelefonyMessageException;
use App\Scheduling\ClockOut;
use App\Shift;
use App\ShiftIssue;

class TelefonyCheckOutController extends BaseTelefonyController
{
    /**
     * Return check out response.
     */
    public function checkOutResponse()
    {
        $shift = $this->telefony->activeShiftForClient($this->client);
        if ($shift) {
            $gather = $this->telefony->gather([
                'numDigits' => 1,
                'action' => route('telefony.check-out', [$shift]),
            ]);
            $this->telefony->say(
                sprintf('If this is %s clocking out, press 2<PAUSE>' .
                    'Press 3 if this is not %s<PAUSE>' .
                    'To return to the main menu, press 0.', $shift->caregiver->firstname, $shift->caregiver->firstname),
                $gather
            );
            return $this->telefony->response();
        }
        return $this->enterPhoneNumberDigits();
    }

    /**
     * Check in caregiver if identity confirmed by pressing 1.
     */
    public function checkOut(Shift $shift)
    {
        switch ($this->request->input('Digits')) {
            case 0:
                return $this->mainMenuResponse();
            case 2:
                return $this->checkForInjuryResponse($shift);
            case 3:
                return $this->enterPhoneNumberDigits();
        }
        return $this->checkOutResponse();
    }

    /**
     * Allow the caregiver to enter the last 4 digits of their phone number for identification
     */
    public function enterPhoneNumberDigits()
    {
        $gather = $this->telefony->gather([
            'numDigits' => 4,
            'action' => route('telefony.check-out.accept-digits')
        ]);
        $this->telefony->say(
            'Please enter the last 4 digits of your phone number for identification',
            $gather
        );
        return $this->telefony->response();
    }

    /**
     * Receive the 4 digits and find the caregiver
     */
    public function acceptPhoneNumberDigits()
    {
        $iteration = $this->request->input('iteration', 0);
        $digits = $this->request->input('Digits');

        if ($caregiver = $this->telefony->findCaregiverByLastDigits($digits, $iteration)) {
            $gather = $this->telefony->gather([
                'numDigits' => 1,
                'action' => route('telefony.check-out', [$caregiver])
            ]);
            if ($caregiver->isClockedIn()) {
                $this->telefony->say(
                    sprintf('If this is %s, press 2 to clock out<PAUSE>press 3 to re-enter.', $caregiver->firstname),
                    $gather
                );
            }
            else {
                $this->telefony->say(
                    sprintf('%s is not clocked in<PAUSE>press 3 to re-enter<PAUSE>press 0 to return to the main menu.', $caregiver->firstname),
                    $gather
                );
            }
        }
        else {
            $this->telefony->say(
                sprintf('There were no matches for, %s<PAUSE>', implode(',,', str_split($digits)))
            );
            $this->telefony->redirect(route('telefony.check-out.enter-digits'));
        }

        return $this->telefony->response();
    }

    /**
     * Ask if there were any injuries or issues
     */
    public function checkForInjuryResponse(Shift $shift) {
        $gather = $this->telefony->gather([
            'timeout' => 5,
            'numDigits' => 1,
            'action' => route('telefony.check-out.check-for-injury', [$shift]),
        ]);
        $this->telefony->say(
            'Were you injured on your shift? Press 1 if there were no injuries. Press 2 if you suffered an injury or unusual circumstances.',
            $gather
        );

        // Redirect loop if nothing is entered
        $this->telefony->redirect(
            route('telefony.check-out.check-for-injury', [$shift])
        );

        return $this->telefony->response();
    }

    /**
     * Receive the injury response
     */
    public function checkForInjuryAction(Shift $shift) {
        switch ($this->request->input('Digits')) {
            case 1:
                return $this->checkForActivitiesResponse($shift);
            case 2:
                $issue = new ShiftIssue();
                $issue->caregiver_injury = true;
                $issue->comments = 'Injury recorded via Telefony System';
                $shift->issues()->save($issue);

                $this->telefony->say('We will be in touch with you regarding your injury.  Please continue clocking out.');
                $this->telefony->redirect(route('telefony.check-out.check-for-activities', [$shift]));
                return $this->telefony->response();
        }

        return $this->checkForInjuryResponse($shift);
    }

    /**
     * Ask for activity codes
     */
    public function checkForActivitiesResponse(Shift $shift) {
        $gather = $this->telefony->gather([
            'timeout' => 30,
            'finishOnKey' => '#',
            'action' => route('telefony.check-out.confirm-activity', [$shift]),
        ]);

        $this->telefony->say(
            'Please enter the numerical code of any activity performed on your shift followed by a pound sign. 
        If you are finished recording activities press the pound sign to finalize your clock out.  
        To hear the list of activities press 0 followed by the pound sign.',
            $gather
        );

        // Finalize if no digits are entered
        $this->telefony->redirect(route('telefony.check-out.finalize', [$shift]));

        return $this->telefony->response();
    }

    /**
     * Read out activity codes
     */
    public function sayAllActivities(Shift $shift) {
        $gather = $this->telefony->gather([
            'timeout' => 10,
            'finishOnKey' => '#',
            'action' => route('telefony.confirm_activity'),
        ]);

        $this->telefony->say(
            'The activity codes are as follows.  You may enter them at any time followed by the pound sign.
           To stop the read-out and go back, press pound sign at any time',
            $gather
        );

        foreach($shift->business->allActivities() as $activity) {
            $codeReadout = implode(',,', str_split($activity->code));
            $this->telefony->say(
                ' .. ' . $codeReadout . ', ' . $activity->name . '.',
                $gather
            );
        }

        $this->telefony->say(
            'To repeat this list, press 0 followed by the pound sign.',
            $gather
        );

        // Finalize if no digits are entered
        $this->telefony->redirect(route('telefony.check_for_activities'));

        return $this->telefony->response();
    }

    /**
     * Confirm an entered activity code
     */
    public function confirmActivity(Shift $shift) {
        $code = $this->request->input('Digits');

        if ($code == 0) {
            return $this->sayAllActivities($shift);
        }

        if ($activity = $shift->business->findActivity($code)) {
            $gather = $this->telefony->gather([
                'timeout' => 10,
                'numDigits' => 1,
                'action' => route('telefony.check-out.record-activity', [$shift, $activity]),
            ]);
            $this->telefony->say(
                sprintf('You have entered, %s.  If this is correct, Press 1<PAUSE>If this is incorrect, Press 2.', $activity->name),
                $gather
            );

            // Redirect back if nothing is entered
            $this->telefony->redirect(route('telefony.check-out.check-for-activities', [$shift]));

            return $this->telefony->response();
        }

        $this->telefony->say('You have entered an invalid activity code.');
        $this->telefony->redirect(route('telefony.check-out.check-for-activities', [$shift]));
        return $this->telefony->response();
    }

    /**
     * Record an activity after confirmation
     */
    public function recordActivity(Shift $shift, Activity $activity) {

        if ($this->request->input('Digits') == 1) {
            $shift->activities()->attach($activity->id);
            $this->telefony->say('The activity has been recorded.');
            $this->telefony->redirect(route('telefony.check-out.check-for-activities'));
            return $this->telefony->response();
        }

        return $this->checkForActivitiesResponse($shift);
    }

    /**
     * Complete the check out
     */
    public function finalizeCheckOut(Shift $shift) {
        // If not private pay, one ADL is required
        if ($shift->client->client_type != 'private_pay') {
            if (!$shift->activities->count()) {
                $this->telefony->say('You must record at least one activity for this client.');
                $this->telefony->redirect(route('telefony.check-out.check-for-activities', [$shift]));
                return $this->telefony->response();
            }
        }

        try {
            $clockOut = new ClockOut($shift->caregiver);
            $clockOut->setNumber($this->number->national_number);

            // Allow clock out but mark it unverified if clocking out from the wrong number
            if ($shift->client_id != $this->client->id) {
                $clockOut->setManual();
            }

            if ($clockOut->clockOut($shift)) {
                $this->telefony->say('You have successfully clocked out.<PAUSE>Thank you. Good bye.');
                return $this->telefony->response();
            }
        }
        catch(\Exception $e) {}

        throw new TelefonyMessageException('There was an error clocking out.  Please try again.  You do not have to re-enter any activities.');
    }
}