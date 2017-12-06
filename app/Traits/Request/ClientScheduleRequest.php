<?php
namespace App\Traits\Request;

use App\Client;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Rules\ValidStartDate;
use App\Schedule;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

trait ClientScheduleRequest
{
    protected function validateRules(Request $request)
    {
        return [
            'time' => 'required|date_format:H:i:s',
            'duration' => 'required|integer',
            'care_plan_id' => 'nullable|exists:care_plans,id',
            'caregiver_id' => 'nullable|integer',
            'caregiver_rate' => 'nullable|numeric',
            'provider_fee' => 'nullable|numeric',
            'notes' => 'nullable',
            'hours_type' => 'required|in:default,overtime,holiday',
        ];
    }

    protected function validateMessages()
    {
        return [
            'bydays.required_if' => 'At least one day of the week is required.',
        ];
    }

    protected function validateScheduleUpdate(Request $request, Schedule $schedule)
    {
        $rules = $this->validateRules($request) +
            [
                'selected_date' => 'required|date',
                'end_date' => 'nullable|date',
                'interval_type' => 'required|in:weekly,biweekly,monthly,bimonthly',
                'bydays' => ['required_if:interval_type,weekly,biweekly', new ValidStartDate($request->input('selected_date'))],
            ];
        return $this->validateData($request, $rules);
    }

    protected function validateScheduleStore(Request $request)
    {
        $rules = $this->validateRules($request) +
            [
                'start_date' => 'required|date',
                'end_date' => 'nullable|date',
                'interval_type' => 'required|in:weekly,biweekly,monthly,bimonthly',
                'bydays' => ['required_if:interval_type,weekly,biweekly', new ValidStartDate($request->input('start_date'))],
            ];
        return $this->validateData($request, $rules);
    }

    protected function validateScheduleUpdateSingle(Request $request, Schedule $schedule)
    {
        $rules = $this->validateRules($request) +
            [
                'selected_date' => 'required|date',
            ];
        return $this->validateData($request, $rules);
    }

    protected function validateScheduleStoreSingle(Request $request)
    {
        $rules = $this->validateRules($request) +
            [
                'start_date' => 'required|date',
            ];
        return $this->validateData($request, $rules);
    }

    public function calculateClientWeeklyHours(Schedule $schedule)
    {
        $client = $schedule->client;

        // Get the first relevant week
        $date = $schedule->getStartDateTime();
        if ($date < Carbon::now()) {
            $date = Carbon::now();
        }
        $start = $date->copy()->startOfWeek()->setTimezone('UTC');
        $end = $date->copy()->endOfWeek()->setTimezone('UTC');

        $events = $client->getEvents($start, $end, true);

        $minutes = 0;
        foreach($events as $event) {
            $minutes += $event['duration'];
        }

        return $minutes / 60;
    }

    protected function weeklyHoursGreaterThanMax(Schedule $schedule)
    {
        return ($this->calculateClientWeeklyHours($schedule) > $schedule->client->max_weekly_hours);
    }

    protected function validateData(Request $request, $rules = null, $messages = null)
    {
        if (!$messages) $messages = $this->validateMessages();
        if (!$rules) $rules = $this->validateRules();
        $data = $request->validate($rules, $messages);

        // Filter dates
        $dateFields = ['start_date', 'selected_date', 'end_date'];
        foreach($dateFields as $dateField) {
            if (isset($data[$dateField])) $data[$dateField] = filter_date($data[$dateField]);
        }

        return $data;
    }
}