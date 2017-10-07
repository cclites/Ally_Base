<?php

namespace App\Http\Controllers\Api;

use App\Client;
use App\Exceptions\TelefonyMessageException;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Twilio\Twiml;
use App\Schedule;
use App\Caregiver;
use App\PhoneNumber;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CaregiverShiftController extends Controller
{
    protected $number;
    protected $request;

    public function __construct(Request $request, PhoneNumber $phoneNumber)
    {
        $this->request = $request;
        $this->number = $phoneNumber->input($request->input('From'));
    }

    /**
     * Return caregiver call in greeting in TwiML.
     */
    public function greeting()
    {
        $response = new Twiml;
        try {
            if ($shift = $this->activeShiftForNumber($this->number)) {
                $gather = $response->gather([
                    'numDigits' => 1,
                    'action' => route('telefony.check_out', [], false),
                ]);
                $gather->say(view('caregivers.voice.greeting_clock_out', compact('schedule'))->render());
            }
            else {
                $schedule = $this->scheduledShiftForNumber($this->number);
                $gather = $response->gather([
                    'numDigits' => 1,
                    'action' => route('telefony.check_in', [], false),
                ]);
                $gather->say(view('caregivers.voice.greeting_clock_in', compact('schedule'))->render());
            }
            return $this->response($response);
        }
        catch(TelefonyMessageException $e) {
            $response->say($e->getMessage());
            return $this->response($response);
        }
        catch(\Exception $e) {
            \Log::error('Error Code: ' . $e->getCode() . ' Message: ' . $e->getMessage() . ' Line: ' . $e->getFile() . ':' . $e->getLine());
            $response->say("Unknown error.  Please hang up and try again.");
            return $this->response($response);
        }
    }

    /**
     * Handle check in/out.
     */
    public function checkInOrOut()
    {
        switch ($this->request->input('Digits')) {
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
    public function checkIn()
    {
        switch ($this->request->input('Digits')) {
            case 0:
                return $this->mainMenuResponse();
            case 1:
                return $this->checkInFirstCaregiver();
            case 2:
                return $this->enterCaregiverResponse();
        }
        return $this->checkInResponse();
    }

    /**
     * Check in caregiver if identity confirmed by pressing 1.
     */
    public function checkOut()
    {
        switch ($this->request->input('Digits')) {
            case 0:
                return $this->mainMenuResponse();
            case 1:
                return $this->checkOutFirstCaregiver();
            case 2:
                return $this->enterCaregiverResponse();
        }
        return $this->checkOutResponse();
    }

    /**
     * Return main menu response.
     */
    private function mainMenuResponse()
    {
        $response = new Twiml();
        $response->say('Returning to the main menu');
        $response->redirect('/api/caregiver/greeting', ['method' => 'GET']);
        return $this->response($response);
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
        $schedule = $this->scheduledShiftForNumber($this->number);
        $gather->say(
            sprintf('If this is %s clocking in, press 1.  To return to the main menu, press 0.', $schedule->caregiver->firstname)
        );
        return $this->response($response);
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
            'action' => '/api/caregiver/check-out',
        ]);
        $schedule = $this->scheduledShiftForNumber($this->number);
        $gather->say(
            sprintf('If this is %s clocking out, press 1. To return to the main menu, press 0.', $schedule->caregiver->firstname)
        );
        return $this->response($response);
    }

    /**
     * Check in caregiver.
     */
    private function checkInFirstCaregiver()
    {
        $response = new Twiml;

        $schedule = $this->scheduledShiftForNumber($this->number);
        $shift = new Shift([
            'client_id' => $schedule->client_id,
            'business_id' => $schedule->business_id,
            'schedule_id' => $schedule->id,
            'checked_in_number' => $this->number->id,
            'verified' => true,
        ]);

        if ($schedule->caregiver->isClockedIn()) {
            $response->say('You are already clocked in.  Please clock out first and then clock in.');
            return $this->response($response);
        }

        if ($schedule->caregiver->shifts()->save($shift)) {
            $response->say('You have clocked in.  Please remember to call back and check-out. Good bye.');
        }
        else {
            $response->say('There was an error clocking in.  Please hang up and try again.');
        }

        return $this->response($response);
    }

    /**
     * Check out caregiver.
     */
    private function checkOutFirstCaregiver()
    {
        $response = new Twiml;
        $shift = $this->activeShiftForNumber($this->number);

        if (!$shift) {
            $response->say('The active shift could not be found.  Please hang up and try again.');
            return $this->response($response);
        }

        $update = $shift->update([
            'checked_out_time' => (new \DateTime())->format('Y-m-d H:i:s'),
            'checked_out_number' => $this->number->id,
        ]);

        if ($update) {
            $response->say('You have successfully clocked out.  Thank you. Good bye.');
        }
        else {
            $response->say('There was an error clocking out.  Please hang up and try again.');
        }

        return $this->response($response);
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
        return $this->response($response);
    }

    /**
     * Find an active shift based on the client phone number
     *
     * @param \App\PhoneNumber $number
     *
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    private function activeShiftForNumber(PhoneNumber $number)
    {
        $national_number = $number->national_number;

        $client = Client::whereHas('phoneNumbers', function($q) use ($national_number) {
            $q->where('national_number', $national_number);
        })->first();

        $shift = Shift::where('client_id', $client->id)
                      ->whereNull('checked_out_time')
                      ->orderBy('checked_in_time', 'DESC')
                      ->first();

        return $shift;
    }

    /**
     * Find a scheduled shift based on the client phone number
     *
     * @param $number
     *
     * @return Schedule
     * @throws \App\Exceptions\TelefonyMessageException
     */
    private function scheduledShiftForNumber(PhoneNumber $number)
    {
        $national_number = $number->national_number;
        $cacheKey = 'twilio_schedule_' . $national_number;

        if (Cache::has($cacheKey)) {
            $schedule_id = Cache::get($cacheKey);
            return Schedule::find($schedule_id);
        }

        $client = Client::whereHas('phoneNumbers', function($q) use ($national_number) {
            $q->where('national_number', $national_number);
        })->first();

        if (!$client) {
            throw new TelefonyMessageException('The number you have called from was not recognized in our system.');
        }

        $start = new \DateTime('-4 hours');
        $end = new \DateTime('+12 hours'); // determine if event's end time has passed in view

        $events = $client->getEvents($start, $end);
        if (empty($events)) {
            throw new TelefonyMessageException('There were no scheduled shifts found for this client. You will need to manually track your time.');
        }

        // Find the closest event to the current time
        $now = new Carbon();
        usort($events, function($a, $b) use ($now) {
            $diffA = $now->diffInSeconds(Carbon::instance($a->start));
            $diffB = $now->diffInSeconds(Carbon::instance($b->start));
            if ($diffA == $diffB) {
                return 0;
            }
            return ($diffA < $diffB) ? -1 : 1;
        });
        $event = current($events);

        $schedule = Schedule::with(['caregiver.user', 'client.business', 'client.user'])->find($event['schedule_id']);
        if (!$schedule) {
            throw new TelefonyMessageException('Error looking up scheduled shift.');
        }

        if (!$schedule->caregiver) {
            if (count($events) > 1) {
                $event = next($events);
            }
            $schedule = Schedule::with(['caregiver.user', 'client.business', 'client.user'])->find($event['schedule_id']);
            if (!$schedule || !$schedule->caregiver)
                throw new TelefonyMessageException('There is no caregiver assigned to this schedule.');
        }

        Cache::set($cacheKey, $schedule->id, 5);
        return $schedule;
    }

    /**
     * All twiml responses need the text/xml content-type.
     */
    private function response($response)
    {
        return response($response)->header('Content-Type', 'text/xml');
    }
}
