<?php

namespace App\Http\Controllers\Api\Telefony;

use App\Caregiver;
use App\Exceptions\TelefonyMessageException;
use App\Shifts\ClockIn;
use App\Events\ShiftFlagsCouldChange;

class TelefonyCheckInController extends BaseVoiceController
{
    const PromptForCaregiverPhone = 'Please enter your own ten digit phone number for identification';
    const AlreadyClockedOutMessage = 'You are already clocked in.  Please clock out first.';

    /**
     * Return check in response.
     *
     * @return mixed
     * @throws TelefonyMessageException
     */
    public function checkInResponse()
    {
        $schedule = $this->telefony->scheduledShiftForClient($this->client);
        if ($schedule) {
            \Log::info('Schedule found.');
            $gather = $this->telefony->gather([
                'numDigits' => 1,
                'action' => route('telefony.check-in', [$schedule->caregiver])
            ]);
            $this->telefony->repeat(
                sprintf('If this is %s clocking in, press 1<PAUSE>
            Press 3 if this is not %s<PAUSE>
            To return to the main menu, press 0.<PAUSE>', $schedule->caregiver->firstname, $schedule->caregiver->firstname),
                $gather
            );
            return $this->telefony->response();
        }
        \Log::info('Schedule not found.');

        return $this->enterPhoneNumberDigits();
    }

    /**
     * Check in caregiver if identity confirmed by pressing 1.
     *
     * @param Caregiver $caregiver
     * @return mixed
     * @throws TelefonyMessageException
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
            'numDigits' => 10,
            'action' => route('telefony.check-in.accept-digits')
        ]);
        $this->telefony->say(
            self::PromptForCaregiverPhone,
            $gather
        );
        return $this->telefony->response();
    }

    /**
     * Receive the 4 digits and find the caregiver
     */
    public function acceptPhoneNumberDigits()
    {
        $digits = $this->request->input('Digits');

        if (strlen($digits) !== 10) {
            if ($digits == 0) {
                return $this->mainMenuResponse();
            }
            return $this->enterPhoneNumberDigits();
        }

        if ($caregiver = $this->telefony->getCaregiverFromPhoneNumber($this->client, $digits)) {
            $gather = $this->telefony->gather([
                'numDigits' => 1,
                'action' => route('telefony.check-in', [$caregiver])
            ]);
            $this->telefony->repeat(
                sprintf('If this is %s, press 1 to finish clocking in<PAUSE>press 3 to re-enter.<PAUSE>press 0 to return to the main menu<PAUSE>', $caregiver->firstname),
                $gather
            );
        } else {
            $this->telefony->say(
                sprintf('There were no matches for, %s<PAUSE>', implode(',,', str_split($digits)))
            );
            $this->telefony->redirect(route('telefony.check-in.enter-digits'));
        }

        return $this->telefony->response();
    }

    /**
     * Check in caregiver.
     *
     * @param Caregiver $caregiver
     * @return mixed
     * @throws TelefonyMessageException
     */
    protected function checkInCaregiver(Caregiver $caregiver)
    {
        $clockIn = new ClockIn($caregiver);
        $clockIn->setNumber($this->number->national_number);

        if ($caregiver->isClockedIn()) {
            throw new TelefonyMessageException(self::AlreadyClockedOutMessage);
        }

        // Try to find schedule with caregiver
        if ($schedule = $this->telefony->scheduledShiftForClient($this->client, $caregiver->id)) {
            try {
                if ($shift = $clockIn->clockIn($schedule)) {
                    $this->telefony->say('You have successfully clocked in.  Please remember to call back and clock out at the end of your shift. Good bye.');
                    return $this->telefony->response();
                }
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
            }
        } else {
            try {
                if ($shift = $clockIn->clockInWithoutSchedule($this->client)) {
                    $this->telefony->say('You have successfully clocked in.  Please remember to call back and clock out at the end of your shift. Good bye.');
                    return $this->telefony->response();
                }
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
            }
        }

        throw new TelefonyMessageException('There was an error clocking in .. Please try again.');
    }
}
