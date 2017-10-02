<?php

namespace App\Responses\Resources;

use App\Schedule as ScheduleModel;
use App\Scheduling\RuleGenerator;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class Schedule implements Responsable
{
    protected $schedule;

    public function __construct(ScheduleModel $schedule)
    {
        $this->schedule = $schedule;
    }

    public function toArray()
    {
        $rrule = false;
        if ($this->schedule->rrule) {
            $rrule = $this->schedule->rrule;
            $rule = (new RuleGenerator())->rrule($this->schedule->rrule);
        }
        $end_time = (new \DateTime('23:00'))->add(new \DateInterval('PT' . $this->schedule->duration . 'M'))->format('H:i:s');

        return [
            'id' => $this->schedule->id,
            'start_date' => $this->schedule->start_date,
            'end_date' => $this->schedule->end_date,
            'time' => $this->schedule->time,
            'duration' => $this->schedule->duration,
            'single' => $this->schedule->isSingle(),
            'interval_type' => ($rrule) ? $rule->getIntervalType() : null,
            'freq' => ($rrule) ? $rule->freq : null,
            'interval' => ($rrule) ? $rule->interval: null,
            'bydays' => ($rrule) ? array_map(function($value) { return substr($value, -2); }, (array) $rule->bydays) : [],
            'bymonthdays' => ($rrule) ? $rule->bymonthdays : [],
            'client_id' => $this->schedule->client_id,
            'caregiver_id' => $this->schedule->caregiver_id,
            'business_id' => $this->schedule->business_id,
            'scheduled_rate' => $this->schedule->scheduled_rate,
        ];
    }

    public function __toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return new JsonResponse($this->toArray());
    }
}
