<?php

namespace App\Http\Controllers\Api;

use Twilio\Twiml;
use App\Schedule;
use App\Caregiver;
use App\PhoneNumber;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Scheduling\ScheduleAggregator;

class CaregiverShiftController extends Controller
{
    /**
     * Return caregiver call in greeting in TwiML.
     */
    public function greeting(Request $request, PhoneNumber $phoneNumber)
    {
        $response = new Twiml;
        $number = $phoneNumber->input($request->input('From'))->numberOnly();
        $schedule = $this->scheduleForNumber($number);
        if (! $schedule) {
            $response->say("Hello. There is no shift scheduled for $number within 90 minutes of now.");
            return $this->response($response);
        }
        $gather = $response->gather([
            'numDigits' => 1,
            'action' => '/api/caregiver/check-in-or-out',
        ]);
        $gather->say(view('caregivers.voice.greeting', compact('schedule'))->render());
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
        return $this->mainMenuResponse();
    }

    /**
     * Check in caregiver if identity confirmed by pressing 1.
     */
    public function checkIn(Request $request)
    {
        switch ($request->input('Digits')) {
            case 1:
                return $this->checkInCaregiver();
            case 2:
                return $this->enterCaregiverResponse();
        }
        return $this->mainMenuResponse();
    }

    /**
     * Return main menu response.
     */
    private function mainMenuResponse()
    {
        $response = new Twiml();
        $response->say('Returning to the main menu');
        $response->redirect('/api/caregiver/greeting', ['method' => 'GET']);
        return response($response)->header('Content-Type', 'text/xml');
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
            'action' => '/api/caregiver/check-in',
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
            'action' => '/api/caregiver/check-in',
        ]);
        $gather->say('If this is Beth checking out, press 1.  If this is incorrect, press 2.');
        return response($response)->header('Content-Type', 'text/xml');
    }

    /**
     * Check in caregiver.
     */
    private function checkInCaregiver()
    {
        $response = new Twiml;
//        $shift = Caregiver::find(3)->startShift();
        $response->say('You have checked in.  Please remember to call back and check-out. Good bye.');
        return response($response)->header('Content-Type', 'text/xml');
    }

    /**
     * Return select caregiver response.
     */
    private function enterCaregiverResponse()
    {
        $response = new Twiml;
        $gather = $response->gather([
            'numDigits' => 6,
            'action' => '/api/caregiver/enter-id',
        ]);
        $gather->say('Please enter your caregiver ID now');
        return response($response)->header('Content-Type', 'text/xml');
    }

    /**
     * Find schedule for number with 90 minutes of now.
     */
    private function scheduleForNumber($number)
    {
        $number = (new PhoneNumber)->input($number)->national_number;
        $number = PhoneNumber::with('client.schedules')
            ->where('national_number', $number)
            ->first();
        if (empty($number->user->role->schedules)) {
            return false;
        }
        $schedules = $number->client->schedules;
        $aggregator = new ScheduleAggregator();
        foreach($schedules as $schedule) {
            $title = ($schedule->client) ? $schedule->client->name() : 'Unknown Client';
            $aggregator->add($title, $schedule);
        }
        // TODO: change -390 and -210 to -90 and +90
        $start = new \DateTime('-390 minutes');
        $end = new \DateTime('-210 minutes');
        $occurrences = $aggregator->events($start, $end);
        if (empty($occurrences)) {
            return false;
        }
        $scheduleIds = array_map(function($occurrence) {
            return $occurrence['schedule_id'];
        }, $occurrences);

        return Schedule::with(['caregiver.user', 'client.business', 'client.user'])
            ->whereIn('id', $scheduleIds)->first();
    }

    /**
     * All twiml responses need the text/xml content-type.
     */
    private function response($response)
    {
        return response($response)->header('Content-Type', 'text/xml');
    }
}
