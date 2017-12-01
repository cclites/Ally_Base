<?php


namespace App\Traits\Request;


use App\Schedule;
use Illuminate\Http\Request;

trait ClientScheduleRequest
{
    protected function validateRules()
    {
        return [
            'end_date' => 'nullable|date',
            'time' => 'required|date_format:H:i:s',
            'duration' => 'required|integer',
            'interval_type' => 'required|in:weekly,biweekly,monthly,bimonthly',
            'bydays' => ['required_if:interval_type,weekly,biweekly', new ValidStartDate($request->input('start_date'))],
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
        $rules = $this->validateRules();
        $rules['selected_date'] = 'required|date';
        $data = $request->validate($rules, $this->validateMessages());

    }

    protected function validateScheduleStore(Request $request)
    {
        $rules = $this->validateRules();
        $rules['start_date'] = 'required|date';
        $data = $request->validate($rules, $this->validateMessages());

    }

    protected function calculateClientWeeklyHours(Schedule $schedule)
    {
        // TODO
    }
}