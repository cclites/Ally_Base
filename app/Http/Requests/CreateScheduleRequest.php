<?php
namespace App\Http\Requests;

use App\Business;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class CreateScheduleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules() {
        $minDate = Carbon::parse('2017-01-01');
        $maxDate = Carbon::now()->addDays(735); // A little over 2 years
        return [
            'starts_at' => 'required|integer|min:' . $minDate->getTimestamp() . '|max:' . $maxDate->getTimestamp(),
            'duration' => 'required|numeric|min:1',
            'client_id' => 'required|exists:clients,id',
            'caregiver_id' => 'nullable|exists:caregivers,id',
            'fixed_rates' => 'required|boolean',
            'caregiver_rate' => 'nullable|numeric|min:0|max:999.99',
            'client_rate' => 'nullable|numeric|min:0|max:999.99',
            'provider_fee' => 'nullable|numeric|min:0|max:999.99',
            'caregiver_rate_id' => 'nullable|exists:rate_codes,id',
            'client_rate_id' => 'nullable|exists:rate_codes,id',
            'notes' => 'nullable|max:1024',
            'hours_type' => 'required|in:default,overtime,holiday',
            'overtime_duration' => 'nullable|numeric|min:0|max:' . (int) $this->input('duration'),
            'interval_type' => 'nullable|in:weekly,biweekly,monthly,bimonthly',
            'recurring_end_date' => 'nullable|integer|min:' . (int) $this->input('starts_at') . '|max:' . $maxDate->getTimestamp(),
            'bydays' => 'required_if:interval_type,weekly,biweekly|array',
            'care_plan_id' => 'nullable|exists:care_plans,id',
        ];
    }

    public function messages()
    {
        return [
            'bydays.required_if' => 'At least one day of the week is required.',
            'recurring_end_date.required_if' => 'You must select an end date for a recurring schedule.',
            'recurring_end_date.max' => 'Schedules can are restricted to a 2 year range.  Lower your recurring end date.',
            'recurring_end_date.min' => 'Your recurring end date cannot be before the start date.',
            'starts_at.min' => 'You cannot create schedule entries prior to 2017.',
            'starts_at.max' => 'Schedules can are restricted to a 2 year range.  Lower your start date.',
            'overtime_duration.max' => 'Overtime duration can not exceed schedule duration.',
            'fixed_rates.*' => 'You must select whether the shift is hourly or daily.',
        ];
    }
}
