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
        $array = $this->schedule->toArray();
        unset($array['business']);
        unset($array['note']);

        $array['starts_at'] = $this->schedule->starts_at->toDateTimeString();
        $array['offset'] = $this->schedule->starts_at->format('P');

        if ($shift = $this->schedule->clockedInShift) {
            $array['clocked_in_shift'] = $shift->load('caregiver');
        }

        $array['group_data'] = $this->schedule->getGroupStatistics();

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
