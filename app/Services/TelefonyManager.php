<?php
namespace App\Services;

use App\Caregiver;
use App\Client;
use App\PhoneNumber;
use App\Schedule;
use App\Scheduling\ScheduleAggregator;
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
    public function findCaregiverByLastDigits(Client $client, $digits, $iteration=0)
    {
        $phoneNumberSearch = function($q) use ($digits) {
            $q->where('national_number', 'LIKE', '%' . $digits);
        };

        $caregivers = $client->caregivers()
                             ->whereHas('phoneNumbers', $phoneNumberSearch)
                             ->get();

        $businessCaregivers = $client->business->caregivers()
                                               ->whereHas('phoneNumbers', $phoneNumberSearch)
                                               ->whereNotIn('caregiver_id', $caregivers->pluck('id'))
                                               ->get();

        $caregivers = $caregivers->merge($businessCaregivers)->values();

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
        return response($this->getTwilioResponse())->header('Content-Type', 'text/xml; charset=UTF-8');
    }

    public function gather($options = []) {
        return $this->getTwilioResponse()->gather($options);
    }

    public function say($message, $object=null, $loop=1) {
        $sayOptions = ['voice' => 'alice'];
        if (!$object) $object = $this->getTwilioResponse();
        if (strpos($message, '<PAUSE>') === false) {
            $sayOptions['loop'] = $loop;
            $object->say($message, $sayOptions);
        }
        else {
            $parts = explode('<PAUSE>', $message);
            for ($i=0; $i<$loop; $i++) {
                if (strlen($parts[0])) $object->say($parts[0], $sayOptions);
                for($p=1; $p<count($parts); $p++) {
                    $object->pause();
                    if (strlen($parts[$p])) $object->say($parts[$p], $sayOptions);
                }
            }
        }
    }

    /**
     * Same as say, but will repeat the message a second time
     *
     * @param $message
     * @param null $object
     */
    public function repeat($message, $object=null) {
        $this->say($message, $object, 2);
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
        $start = new Carbon('-12 hours');
        $end = new Carbon('+12 hours');

        $aggregator = app()->make(ScheduleAggregator::class);
        $aggregator->where('client_id', $client->id);
        if ($caregiver_id) {
            $aggregator->where('caregiver_id', $caregiver_id);
        }
        $schedules = $aggregator->getSchedulesBetween($start, $end);

        if (empty($schedules)) {
            return null;
        }

        // Find the closest event to the current time
        $now = Carbon::now();
        $schedules->sort(function($a, $b) use ($now) {
            $diffA = $now->diffInSeconds($a->starts_at);
            $diffB = $now->diffInSeconds($b->starts_at);
            if ($diffA == $diffB) {
                return 0;
            }
            return ($diffA < $diffB) ? -1 : 1;
        });

        foreach($schedules as $schedule) {
            if ($schedule->caregiver) {
                return $schedule;
            }
        }

        return null;
    }

}
