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
        $greeting = new Twiml();
        $greeting->say(
            "Hello and thank you for calling Kevin's Home Care Agency. " .
            "Press 1 to check in.  Press 2 to check out."
        );
        return response($greeting)->header('Content-Type', 'text/xml');
    }
}
