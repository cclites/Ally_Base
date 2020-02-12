<?php

namespace App\Http\Controllers\Api\Telefony;

/**
 * Class CaregiverShiftController
 * @package App\Http\Controllers\Api
 *
 * NOTE: Sessions are not supported by Twilio (stateless), use request parameters or cache
 *
 */
class TelefonyGreetingController extends BaseVoiceController
{

    /**
     * Return caregiver call in greeting in TwiML.
     */
    public function greeting()
    {
        $gather = $this->telefony->gather([
            'timeout' => 15,
            'numDigits' => 1,
            'action' => route('telefony.check-in-or-out'),
        ]);
        $this->telefony->repeat(
            "Press 1 to clock in .. Press 2 to clock out.<PAUSE>",
            $gather
        );
        return $this->telefony->response();
    }

    /**
     * Handle check in/out.
     */
    public function checkInOrOut()
    {
        switch ($this->request->input('Digits')) {
            case 1:
                $this->telefony->redirect(route('telefony.check-in.response'));
                return $this->telefony->response();
            case 2:
                $this->telefony->redirect(route('telefony.check-out.response'));
                return $this->telefony->response();
        }
        return $this->mainMenuResponse();
    }


}
