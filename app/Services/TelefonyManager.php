<?php
namespace App\Services;

use App\Caregiver;
use App\Client;
use App\Exceptions\TelefonyMessageException;
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

        $client = Client::active()
            ->whereHas('phoneNumbers', function ($q) use ($national_number) {
                $q->where('national_number', $national_number);
            })
            ->first();

        Cache::put('telefony_client_' . $national_number, $client, 2);
        return $client;
    }

    /**
     * Lookup Caregiver record from a phone number.
     *
     * @param Client $client
     * @param string $digits
     * @return Caregiver|null
     */
    public function getCaregiverFromPhoneNumber(Client $client, string $digits) : ?Caregiver
    {
        $caregiver = $client->caregivers()
            ->whereHas('phoneNumbers', function ($q) use ($digits) {
                $q->where('national_number', $digits);
            })
            ->first();

        if (empty($caregiver)) {
            $caregiver = $client->business->caregivers()
                ->whereHas('phoneNumbers', function ($q) use ($digits) {
                    $q->where('national_number', $digits);
                })
                ->first();
        }

        return $caregiver;
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

    public function twiml()
    {
        if (!$this->twilioResponse) {
            $this->twilioResponse = new Twiml;
        }
        return $this->twilioResponse;
    }

    public function getTwilioResponse()
    {
        return $this->twiml();
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
