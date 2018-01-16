<?php
namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class CreateScheduleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules() {
        $minDate = Carbon::now()->setTime(0, 0, 0);
        $maxDate = Carbon::now()->addDays(735); // A little over 2 years
        return [
            'starts_at' => 'required|integer|min:' . $minDate->getTimestamp() . '|max:' . $maxDate->getTimestamp(),
            'duration' => 'required|numeric|min:1',
            'client_id' => 'required|exists:clients,id',
            'caregiver_id' => 'nullable|exists:caregivers,id',
            'caregiver_rate' => 'nullable|numeric',
            'provider_fee' => 'nullable|numeric',
            'notes' => 'nullable|max:1024',
            'hours_type' => 'required|in:default,overtime,holiday',
            'overtime_duration' => 'nullable|numeric|min:0|max:' . (int) $this->input('duration'),
            'interval_type' => 'nullable|in:weekly,biweekly,monthly,bimonthly',
            'recurring_end_date' => 'nullable|integer|min:' . (int) $this->input('starts_at') . '|max:' . $maxDate->getTimestamp(),
            'bydays' => 'required_if:interval_type,weekly,biweekly|array',
        ];
    }

    public function messages()
    {
        return [
            'bydays.required_if' => 'At least one day of the week is required.',
            'recurring_end_date.required_if' => 'You must select an end date for a recurring schedule.',
            'recurring_end_date.max' => 'Schedules can are restricted to a 2 year range.  Lower your recurring end date.',
            'recurring_end_date.min' => 'Your recurring end date cannot be before the start date.',
            'starts_at.min' => 'You cannot create past schedules.  The starting date must be today or later.',
            'starts_at.max' => 'Schedules can are restricted to a 2 year range.  Lower your start date.',
            'overtime_duration.max' => 'Overtime duration can not exceed schedule duration.'
        ];
    }
}