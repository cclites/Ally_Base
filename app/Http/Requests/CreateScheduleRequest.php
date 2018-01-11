<?php
namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class CreateScheduleRequest extends FormRequest
{
    public function rules() {
        $minDate = Carbon::now()->setTime(0, 0, 0);
        $maxDate = Carbon::now()->addYears(2);
        return [
            'starts_at' => 'required|date_format:c|min:' . $minDate . '|max:' . $maxDate,
            'duration' => 'required|numeric',
            'client_id' => 'required|exists:clients',
            'caregiver_id' => 'nullable|exists:caregivers',
            'caregiver_rate' => 'nullable|numeric',
            'provider_fee' => 'nullable|numeric',
            'notes' => 'nullable|max:1024',
            'hours_type' => 'required|in:default,overtime,holiday',
            'overtime_duration' => 'nullable|numeric|min:0|max:' . $this->input('duration'),
            'interval_type' => 'nullable|in:weekly,biweekly,monthly,bimonthly',
            'recurring_end_date' => 'required_if:interval_type|date_format:Y-m-d|after:starts_at|max:' . $maxDate,
            'bydays' => 'required_if:interval_type,weekly,biweekly|array',
        ];
    }

    public function messages()
    {
        return [
            'bydays.required_if' => 'At least one day of the week is required.',
            'recurring_end_date.required_if' => 'You must select an end date for a recurring schedule.',
            'recurring_end_date.max' => 'Schedules can are restricted to a 2 year range.  Lower your recurring end date.',
            'starts_at|min' => 'You cannot create past schedules.  The starting date must be today or later.',
            'starts_at|max' => 'Schedules can are restricted to a 2 year range.  Lower your start date.',
            'overtime_duration|max' => 'Overtime duration can not exceed schedule duration.'
        ];
    }
}