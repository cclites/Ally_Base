<?php
namespace App\Http\Requests;

use Carbon\Carbon;

class UpdateScheduleRequest extends CreateScheduleRequest
{
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
            'care_plan_id' => 'nullable|exists:care_plans,id',
            'status' => 'sometimes|required|string|min:2',
            'services' => 'array|required_without:service_id',
            'services.*.id' => 'nullable|exists:schedule_services,id',
            'services.*.service_id' => 'required_with:services|exists:services,id',
            'services.*.payer_id' => 'nullable|exists:payers,id',
            'services.*.hours_type' => 'required_with:services|string|in:default,overtime,holiday',
            'services.*.duration' => 'required_with:services|numeric|min:0|max:999.99',
            'services.*.client_rate' => 'required_with:services|numeric|min:0|max:999.99',
            'services.*.caregiver_rate' => 'required_with:services|numeric|min:0|max:999.99',
        ];
    }

    public function messages()
    {
        return [
            'starts_at.min' => 'You cannot edit schedules prior to 2017.',
            'starts_at.max' => 'Schedules can are restricted to a 2 year range.  Lower your start date.',
            'overtime_duration.max' => 'Overtime duration can not exceed schedule duration.',
            'fixed_rates.*' => 'You must select whether the shift is hourly or daily.',
            'status' => 'The schedule status must be selected in the notes tab.',
        ];
    }
}
