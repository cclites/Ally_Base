<?php

namespace App\Http\Controllers\Api;

use App\Activity;
use App\Client;
use App\Exceptions\TelefonyMessageException;
use App\Scheduling\ClockIn;
use App\Scheduling\ClockOut;
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
    protected $twilioResponse;

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
        if ($shift = $this->activeShiftForNumber($this->number)) {
            $schedule = $shift->schedule;
            $gather = $this->gather([
                'timeout' => 15,
                'numDigits' => 1,
                'action' => route('telefony.check_out', [], false),
            ]);
            $this->say(view(
                'caregivers.voice.greeting_clock_out', compact('schedule'))->render(),
                $gather
            );
            return $this->response();
        }
        else {
            return $this->checkForNextShift();
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
                return $this->checkForNextShift();
//                return $this->enterCaregiverResponse();
        }
        return $this->checkOutResponse();
    }

    public function checkForNextShift()
    {
        try {
            $schedule = $this->scheduledShiftForNumber($this->number);
            // no TelefonyMessageException, so $schedule was found
            $this->forceCheckout();
            $gather = $this->gather([
                'timeout' => 15,
                'numDigits' => 1,
                'action' => route('telefony.check_in', [], false),
            ]);
            $this->say(
                view('caregivers.voice.greeting_clock_in', compact('schedule'))->render(),
                $gather
            );
            return $this->response();
        }
        catch(TelefonyMessageException $e) {
            $this->say($e->getMessage());
            return $this->response();
        }
    }

    /**
     * Return main menu response.
     */
    private function mainMenuResponse()
    {
        $this->say('Returning to the main menu');
        $this->redirect('/api/caregiver/greeting', ['method' => 'GET']);
        return $this->response();
    }

    /**
     * Return check in response.
     * TODO: pull caregiver from database
     */
    private function checkInResponse()
    {
        $gather = $this->gather([
            'numDigits' => 1,
            'action' => '/api/caregiver/check-in',
        ]);
        $schedule = $this->scheduledShiftForNumber($this->number);
        $this->say(
            sprintf('If this is %s clocking in, press 1.  To return to the main menu, press 0.', $schedule->caregiver->firstname),
            $gather
        );
        return $this->response();
    }

    /**
     * Return check out response.
     * TODO: pull caregiver from database
     */
    private function checkOutResponse()
    {
        $gather = $this->gather([
            'numDigits' => 1,
            'action' => '/api/caregiver/check-out',
        ]);
        $schedule = $this->scheduledShiftForNumber($this->number);
        $this->say(
            sprintf('If this is %s clocking out, press 1. To return to the main menu, press 0.', $schedule->caregiver->firstname),
            $gather
        );
        return $this->response();
    }

    /**
     * Check in caregiver.
     */
    private function checkInFirstCaregiver()
    {
        $schedule = $this->scheduledShiftForNumber($this->number);

        if ($schedule->caregiver->isClockedIn()) {
            $this->say('You are already clocked in.  Please clock out first.');
            return $this->response();
        }

        try {
            $clockIn = new ClockIn($schedule->caregiver);
            $clockIn->setNumber($this->number->national_number);
            if ($clockIn->clockIn($schedule)) {
                $this->removeScheduledShiftCache($this->number->national_number);
                $this->say('You have successfully clocked in.  Please remember to call back and clock out at the end of your shift. Good bye.');
                return $this->response();
            }
        }
        catch (\Exception $e) {}

        $this->say('There was an error clocking in.  Please hang up and try again.');
        return $this->response();

    }

    /**
     * Check out caregiver.
     */
    private function checkOutFirstCaregiver()
    {
        $shift = $this->activeShiftForNumber($this->number);

        if (!$shift) {
            $this->say('The active shift could not be found.  Please hang up and try again.');
            return $this->response();
        }

        return $this->checkForInjuryResponse();
    }

    private function checkForInjuryResponse() {
        $gather = $this->gather([
            'timeout' => 5,
            'numDigits' => 1,
            'action' => route('telefony.check_for_injury'),
        ]);
        $this->say(
            'Were you injured on your shift? Press 1 if there were no injuries. Press 2 if you suffered an injury or unusual circumstances.',
            $gather
        );

        // Redirect loop if nothing is entered
        $this->redirect(route('telefony.check_for_injury'));

        return $this->response();
    }

    public function checkForInjuryAction() {
        switch ($this->request->input('Digits')) {
            case 1:
                return $this->checkForActivitiesResponse();
            case 2:
                $shift = $this->activeShiftForNumber($this->number);
                $issue = new ShiftIssue();
                $issue->caregiver_injury = true;
                $issue->comments = 'Injury recorded via Telefony System';
                $shift->issues()->save($issue);

                $this->say('We will be in touch with you regarding your injury.  Please continue clocking out.');
                $this->redirect(route('telefony.check_for_activities'));
                return $this->response();
        }

        return $this->checkForInjuryResponse();
    }

    public function checkForActivitiesResponse() {
        $gather = $this->gather([
            'timeout' => 30,
            'finishOnKey' => '#',
            'action' => route('telefony.confirm_activity'),
        ]);

        $this->say(
            'Please enter the numerical code of any activity performed on your shift followed by a #. 
        If you are finished recording activities press the # to finalize your clock out.  
        To hear the list of activities press 0 followed by the #.',
            $gather
        );

        // Finalize if no digits are entered
        $this->redirect(route('telefony.finalize_check_out'));

        return $this->response();
    }

    public function sayAllActivities() {
        $gather = $this->gather([
            'timeout' => 10,
            'finishOnKey' => '#',
            'action' => route('telefony.confirm_activity'),
        ]);

        $this->say(
            'The activity codes are as follows.  You may enter them at any time followed by the #.
           To stop the read-out and go back, press # at any time',
            $gather
        );

        $shift = $this->activeShiftForNumber($this->number);
        foreach($shift->business->allActivities() as $activity) {
            $codeReadout = implode(',,', str_split($activity->code));
            $this->say(
                $codeReadout . ', ' . $activity->name,
                $gather
            );
        }

        $this->say(
            'To repeat this list, press 0 followed by the #.',
            $gather
        );

        // Finalize if no digits are entered
        $this->redirect(route('telefony.check_for_activities'));

        return $this->response();
    }

    public function confirmActivity() {
        $shift = $this->activeShiftForNumber($this->number);
        $code = $this->request->input('Digits');

        \Log::info('Telefony activity code entered: ' . $code);

        if ($code == 0) {
            return $this->sayAllActivities();
        }

        if ($activity = $shift->business->findActivity($code)) {
            $gather = $this->gather([
                'timeout' => 10,
                'numDigits' => 1,
                'action' => route('telefony.record_activity', [$activity->id]),
            ]);
            $this->say(
                sprintf('You have entered %s.  If this is correct, Press 1. If this is incorrect, Press 2.', $activity->name),
                $gather
            );

            // Redirect back if nothing is entered
            $this->redirect(route('telefony.check_for_activities'));

            return $this->response();
        }

        $this->say('You have entered an invalid activity code.');
        $this->redirect(route('telefony.check_for_activities'));
        return $this->response();
    }

    public function recordActivity($activity_id) {

        if ($this->request->input('Digits') == 1) {
            Session::remove('current_activity_id');
            $shift = $this->activeShiftForNumber($this->number);
            $shift->activities()->attach($activity_id);
            $this->say('The activity has been recorded.');
            $this->redirect(route('telefony.check_for_activities'));
            return $this->response();
        }

        return $this->checkForActivitiesResponse();
    }

    public function finalizeCheckOut() {
        $shift = $this->activeShiftForNumber($this->number);

        // If not private pay, one ADL is required
        if ($shift->client->client_type != 'private_pay') {
            if (!$shift->activities->count()) {
                $this->say('You must record at least one activity for this client.');
                $this->redirect(route('telefony.check_for_activities'));
                return $this->response();
            }
        }

        try {
            $clockOut = new ClockOut($shift->caregiver);
            $clockOut->setNumber($this->number->national_number);
            if ($clockOut->clockOut($shift)) {
                $this->say('You have successfully clocked out.  Thank you. Good bye.');
                return $this->response();
            }
        }
        catch(\Exception $e) {}

        $this->say('There was an error clocking out.  Please hang up and try again.  You do not have to re-enter any activities.');
        return $this->response();
    }

    public function forceCheckout() {
        if ($shift = $this->activeShiftForNumber($this->number)) {
            $clockOut = new ClockOut($shift->caregiver);
            $issue = new ShiftIssue(['comments' => 'Auto clock out by the next scheduled caregiver using Telefony.']);
            $clockOut->setManual()
                ->clockOut($shift);
            $clockOut->attachIssue($shift, $issue);
        }
    }

    /**
     * Return select caregiver response.
     */
    private function enterCaregiverResponse()
    {
        $gather = $this->gather([
            'numDigits' => 6,
            'action' => '/api/caregiver/enter-id',
        ]);
        $this->say(
            'Please enter your caregiver ID now',
            $gather
        );
        return $this->response();
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

        if ($this->hasScheduledShiftCached($national_number)) {
            $schedule_id = $this->getScheduledShiftCache($national_number);
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
            $diffA = $now->diffInSeconds(Carbon::instance($a['start']));
            $diffB = $now->diffInSeconds(Carbon::instance($b['start']));
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

        $this->setScheduledShiftCache($national_number, $schedule->id);

        return $schedule;
    }

    protected function hasScheduledShiftCached($national_number)
    {
        $cacheKey = 'twilio_schedule_' . $national_number;
        return Cache::has($cacheKey);
    }

    protected function setScheduledShiftCache($national_number, $schedule_id)
    {
        $cacheKey = 'twilio_schedule_' . $national_number;
        Cache::put($cacheKey, $schedule_id, 5);
    }

    protected function getScheduledShiftCache($national_number)
    {
        $cacheKey = 'twilio_schedule_' . $national_number;
        return Cache::get($cacheKey);
    }

    protected function removeScheduledShiftCache($national_number)
    {
        $cacheKey = 'twilio_schedule_' . $national_number;
        Cache::forget($cacheKey);
    }

    private function getTwilioResponse() {
       if (!$this->twilioResponse) {
           $this->twilioResponse = new Twiml;
       }
       return $this->twilioResponse;
    }

    /**
     * All twiml responses need the text/xml content-type.
     */
    private function response()
    {
        return response($this->getTwilioResponse())->header('Content-Type', 'text/xml');
    }

    private function gather($options = []) {
        return $this->getTwilioResponse()->gather($options);
    }

    private function say($message, $object=null) {
        if (!$object) $object = $this->getTwilioResponse();
        return $object->say($message, ['voice' => 'alice']);
    }

    private function redirect($url, $options=[], $object=null) {
        if (!$object) $object = $this->getTwilioResponse();
        return $object->redirect($url, $options);
    }
}
