<?php
namespace App\Http\Controllers\Api;

use App\Caregiver;
use App\Exceptions\TelefonyMessageException;
use App\Scheduling\ClockIn;

class TelefonyCheckInController extends BaseTelefonyController
{
    /**
     * Return check in response.
     */
    public function checkInResponse()
    {
        $schedule = $this->telefony->scheduledShiftForClient($this->client);
        if ($schedule) {
            $gather = $this->telefony->gather([
                'numDigits' => 1,
                'action' => route('telefony.check-in', [$schedule->caregiver])
            ]);
            $this->telefony->say(
                sprintf('If this is %s clocking in, press 1,,,,,,
            Press 3 if this is not %s,,,,,,,
            To return to the main menu, press 0.', $schedule->caregiver->firstname, $schedule->caregiver->firstname),
                $gather
            );
        }
        else {
            return $this->enterPhoneNumberDigits();
        }

        return $this->telefony->response();
    }

    /**
     * Check in caregiver if identity confirmed by pressing 1.
     */
    public function checkIn(Caregiver $caregiver)
    {
        switch ($this->request->input('Digits')) {
            case 0:
                return $this->mainMenuResponse();
            case 1:
                return $this->checkInCaregiver($caregiver);
            case 3:
                return $this->enterPhoneNumberDigits();
        }
        return $this->checkInResponse();
    }

    /**
     * Allow the caregiver to enter the last 4 digits of their phone number for identification
     */
    public function enterPhoneNumberDigits()
    {
        $gather = $this->telefony->gather([
            'numDigits' => 4,
            'action' => route('telefony.check-in.accept-digits')
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
                'action' => route('telefony.check-in', [$caregiver])
            ]);
            $this->telefony->say(
                sprintf('If this is %s, press 1 to finish clocking in,,,,,,press 3 to re-enter.', $caregiver->firstname),
                $gather
            );
        }
        else {
            $this->telefony->say(
                sprintf('There were no matches for %s,,,,', implode(',,', str_split($digits)))
            );
            $this->telefony->redirect('/api/caregiver/check-in/enter-digits');
        }

        return $this->telefony->response();
    }

    /**
     * Check in caregiver.
     */
    protected function checkInCaregiver(Caregiver $caregiver)
    {
        $clockIn = new ClockIn($caregiver);
        $clockIn->setNumber($this->number->national_number);

        if ($caregiver->isClockedIn()) {
            throw new TelefonyMessageException('You are already clocked in.  Please clock out first.');
        }

        // Try to find schedule with caregiver
        if ($schedule = $this->telefony->scheduledShiftForClient($this->client, $caregiver->id)) {
            try {
                if ($clockIn->clockIn($schedule)) {
                    $this->telefony->say('You have successfully clocked in.  Please remember to call back and clock out at the end of your shift. Good bye.');
                    return $this->telefony->response();
                }
            }
            catch (\Exception $e) {
                \Log::error($e->getMessage());
            }
        }
        else {
            try {
                if ($clockIn->clockInWithoutSchedule($this->client->business, $this->client)) {
                    $this->telefony->say('You have successfully clocked in.  Please remember to call back and clock out at the end of your shift. Good bye.');
                    return $this->telefony->response();
                }
            }
            catch (\Exception $e) {
                \Log::error($e->getMessage());
            }
        }

        throw new TelefonyMessageException('There was an error clocking in,,,,,  Please try again.');
    }
}