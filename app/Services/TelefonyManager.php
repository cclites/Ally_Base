<?php
namespace App\Services;

use App\Caregiver;
use App\Client;
use App\PhoneNumber;
use App\Schedule;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Twilio\Twiml;

class TelefonyManager
{
    /**
     * @var \Twilio\Twiml
     */
    protected $twilioResponse;

    public function findClientByNumber(PhoneNumber $number)
    {
        $national_number = $number->national_number;

        if ($cached = Cache::get('telefony_client_' . $national_number)) {
            return $cached;
        }

        $client = Client::whereHas('phoneNumbers', function ($q) use ($national_number) {
            $q->where('national_number', $national_number);
        })->first();

        Cache::put('telefony_client_' . $national_number, $client, 2);
        return $client;
    }

    /**
     * @param $digits
     * @param int $iteration
     * @return Caregiver|null
     */
    public function findCaregiverByLastDigits($digits, $iteration=0)
    {
        $caregivers = Caregiver::whereHas('phoneNumber', function($q) use ($digits) {
            $q->where('national_number', $digits);
        })
            ->orderBy('id')
            ->get();

        if (isset($caregivers[$iteration])) {
            return $caregivers[$iteration];
        }
        return null;
    }

    public function activeShiftForClient(Client $client)
    {
        $shift = Shift::where('client_id', $client->id)
            ->whereNull('checked_out_time')
            ->orderBy('checked_in_time', 'DESC')
            ->first();

        return $shift;
    }

    /**
     * All twiml responses need the text/xml content-type.
     */
    public function response()
    {
        return response($this->getTwilioResponse())->header('Content-Type', 'text/xml');
    }

    public function gather($options = []) {
        return $this->getTwilioResponse()->gather($options);
    }

    public function say($message, $object=null) {
        if (!$object) $object = $this->getTwilioResponse();
        return $object->say($message, ['voice' => 'alice']);
    }

    public function pause($seconds=1, $object=null) {
        if (!$object) $object = $this->getTwilioResponse();
        return $object->pause($seconds);
    }

    public function redirect($url, $options=[], $object=null) {
        if (!$object) $object = $this->getTwilioResponse();
        return $object->redirect($url, $options);
    }

    public function getTwilioResponse() {
        if (!$this->twilioResponse) {
            $this->twilioResponse = new Twiml;
        }
        return $this->twilioResponse;
    }

    /**
     * Find a scheduled shift based on the client phone number
     *
     * @param \App\Client $client
     * @param int $caregiver_id  Limit scheduled shift to this caregiver if provided
     *
     * @return Schedule
     * @throws \App\Exceptions\TelefonyMessageException
     */
    public function scheduledShiftForClient(Client $client, $caregiver_id = null)
    {
        $start = new \DateTime('-12 hours');
        $end = new \DateTime('+12 hours');

        $events = $client->getEvents($start, $end);
        if (empty($events)) {
            return null;
        }

        // Filter events by caregiver if provided
        if ($caregiver_id) {
            $events = array_filter($events, function($event) use ($caregiver_id) {
                return ($event['caregiver_id'] == $caregiver_id);
            });
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

        $schedule = Schedule::with(['client.business', 'caregiver'])->find($event['schedule_id']);
        if (!$schedule) {
            return null;
        }

        if (!$schedule->caregiver) {
            if (count($events) > 1) {
                $event = next($events);
            }
            $schedule = Schedule::with(['caregiver.user', 'client.business', 'client.user'])->find($event['schedule_id']);
            if (!$schedule || !$schedule->caregiver) {
                return null;
            }
        }

        return $schedule;
    }

}