<?php

namespace App\Http\Controllers\Api;

use App\Activity;
use App\Client;
use App\Exceptions\TelefonyMessageException;
use App\Shift;
use App\ShiftIssue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Twilio\Twiml;
use App\Schedule;
use App\Caregiver;
use App\PhoneNumber;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * Class CaregiverShiftController
 * @package App\Http\Controllers\Api
 *
 * NOTE: Sessions are not supported, use request parameters or cache
 *
 */
class CaregiverShiftController extends Controller
{
    protected $number;
    protected $request;

    public function __construct(Request $request, PhoneNumber $phoneNumber)
    {
//        $this->middleware('twilio');
        $this->request = $request;
        if ($request->input('From')) {
            $this->number = $phoneNumber->input($request->input('From'));
        }
    }

    /**
     * Return caregiver call in greeting in TwiML.
     */
    public function greeting()
    {
        $response = new Twiml;
        try {
            if ($shift = $this->activeShiftForNumber($this->number)) {
                $schedule = $shift->schedule;
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
            'checked_in_time' => (new \DateTime())->format('Y-m-d H:i:s'),
            'checked_in_number' => $this->number->national_number,
            'verified' => true,
        ]);

        if ($schedule->caregiver->isClockedIn()) {
            $response->say('You are already clocked in.  Please clock out first.');
            return $this->response($response);
        }

        if ($schedule->caregiver->shifts()->save($shift)) {
            $response->say('You have successfully clocked in.  Please remember to call back and check-out at the end of your shift. Good bye.');
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
        $shift = $this->activeShiftForNumber($this->number);

        if (!$shift) {
            $response = new Twiml;
            $response->say('The active shift could not be found.  Please hang up and try again.');
            return $this->response($response);
        }

        return $this->checkForInjuryResponse();
    }

    private function checkForInjuryResponse() {
        $response = new Twiml;
        $gather = $response->gather([
            'numDigits' => 1,
            'action' => route('telefony.check_for_injury'),
        ]);
        $schedule = $this->scheduledShiftForNumber($this->number);
        $gather->say('Did you suffer any injuries during your shift? Press 1 if you suffered an injury. Press 2 if you were not injured.');
        return $this->response($response);
    }

    public function checkForInjuryAction() {
        switch ($this->request->input('Digits')) {
            case 1:
                $shift = $this->activeShiftForNumber($this->number);
                $issue = new ShiftIssue();
                $issue->caregiver_injury = true;
                $issue->comments = 'Injury recorded via Telefony System';
                $shift->issues()->save($issue);

                $response = new Twiml;
                $response->redirect(route('telefony.check_for_activities'));
                $response->say('We will be in touch with you shortly regarding your injury.');
                return $this->response($response);
            case 2:
                return $this->checkForActivitiesResponse();
        }

        return $this->checkForInjuryResponse();
    }

    public function checkForActivitiesResponse() {
        $response = new Twiml;
        $gather = $response->gather([
            'timeout' => 30,
            'finishOnKey' => '#',
            'action' => route('telefony.confirm_activity'),
        ]);

        $gather->say('Please enter the numerical code of any activity performed on your shift followed by a #. If you are finished recording activities press the # to finalize your clock out.');

        return $this->response($response);
    }

    public function confirmActivity() {
        $shift = $this->activeShiftForNumber($this->number);
        $code = $this->request->input('Digits');
        $response = new Twiml;

        if (!strlen($code)) {
            return $this->finalizeCheckOut();
        }

        if ($activity = $shift->business->findActivity($code)) {
            $gather = $response->gather([
                'numDigits' => 1,
                'action' => route('telefony.record_activity', [$activity->id]),
            ]);
            $gather->say(
                sprintf('You have entered %s.  If this is correct, Press 1. If this is incorrect, Press 2.', $activity->name)
            );
            return $this->response($response);
        }

        $response->say('You have entered an invalid activity code.');
        $response->redirect(route('telefony.check_for_activities'));
        return $this->response($response);
    }

    public function recordActivity($activity_id) {
        $response = new Twiml;

        if ($this->request->input('Digits') == 1) {
            Session::remove('current_activity_id');
            $shift = $this->activeShiftForNumber($this->number);
            $shift->activities()->attach($activity_id);
            $response->say('The activity has been recorded.');
            $response->redirect(route('telefony.check_for_activities'));
            return $this->response($response);
        }

        return $this->checkForActivitiesResponse();
    }

    private function finalizeCheckOut() {
        $response = new Twiml;
        $shift = $this->activeShiftForNumber($this->number);

        $update = $shift->update([
            'checked_out_time' => (new \DateTime())->format('Y-m-d H:i:s'),
            'checked_out_number' => $this->number->national_number,
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
     * @return Shift|null|static
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

        $start = new \DateTime('-1 hours');
        $end = new \DateTime('+1 hours');

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

        Cache::put($cacheKey, $schedule->id, 5);
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
