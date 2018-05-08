<?php

namespace App\Responses\Resources;

use App\Schedule as ScheduleModel;
use App\Scheduling\RuleGenerator;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class Schedule implements Responsable
{
    protected $schedule;

    public function __construct(ScheduleModel $schedule)
    {
        $this->schedule = $schedule;
    }

    public function toArray()
    {
        $array = $this->schedule->toArray();
        unset($array['business']);
        unset($array['note']);

        $timestamp = $this->schedule->starts_at->timestamp;
        $array['starts_at'] = $timestamp;
        $array['is_past'] = $this->schedule->starts_at < Carbon::now($this->schedule->business->timezone)->setTime(0, 0);;

        return $array;
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
