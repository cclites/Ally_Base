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
            'action' => '/api/caregiver/checkin',
        ]);
        $gather->say(
            "Hello and thank you for calling Kevin's Home Care Agency. " .
            "Press 1 to check in.  Press 2 to check out."
        );
        return response($response)->header('Content-Type', 'text/xml');
    }

    /**
     * Handle caregiver checkin.
     */
    public function checkin(Request $request)
    {
        $response = new Twiml;
        $response->say('You have pressed ' . $request->input('Digits'));
        return response($response)->header('Content-Type', 'text/xml');
    }
}
