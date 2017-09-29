<?php

namespace App\Http\Controllers\Api;

use Twilio\Twiml;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CaregiverShiftController extends Controller
{
    /**
     * Return caregiver call in greeting in TwiML.
     * TODO: replace placeholder text with real greeting
     */
    public function greeting()
    {
        $response = new Twiml;
        $gather = $response->gather([
            'numDigits' => 1,
            'action' => '/api/caregiver/check-in-or-out',
        ]);
        $gather->say(
            "Hello and thank you for calling Kevin's Home Care Agency. " .
            "Press 1 to check in. Press 2 to check out."
        );
        return response($response)->header('Content-Type', 'text/xml');
    }

    /**
     * Handle check in/out.
     */
    public function checkInOrOut(Request $request)
    {
        switch ($request->input('Digits')) {
            case 1:
                return $this->checkInResponse();
            case 2:
                return $this->checkOutResponse();
        }
        $response = new Twiml();
        $response->say('Returning to the main menu');
        $response->redirect('/api/caregiver/greeting');
        return $response;
    }

    /**
     * Return check in response.
     * TODO: pull caregiver from database
     */
    private function checkInResponse()
    {
        $response = new Twiml;
        $gather = $response->gather([
            'numDigits' => 1,
            'action' => '/api/caregiver/confirm-identity',
        ]);
        $gather->say('If this is Beth checking in, press 1.  If this is incorrect, press 2.');
        return response($response)->header('Content-Type', 'text/xml');
    }

    /**
     * Return check out response.
     * TODO: pull caregiver from database
     */
    private function checkOutResponse()
    {
        $response = new Twiml;
        $gather = $response->gather([
            'numDigits' => 1,
            'action' => '/api/caregiver/confirm-identity',
        ]);
        $gather->say('If this is Beth checking out, press 1.  If this is incorrect, press 2.');
        return response($response)->header('Content-Type', 'text/xml');
    }
}
