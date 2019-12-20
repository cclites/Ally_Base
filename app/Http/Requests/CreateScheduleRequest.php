<?php
namespace App\Http\Requests;

use App\Rules\ValidEffectivePayer;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Schedule;
use Illuminate\Validation\Rule;

class CreateScheduleRequest extends BusinessClientRequest
{
    public function rules() {
        $minDate = '2018-01-01';
        $maxDate = Carbon::now()->addDays(735)->toDateString(); // A little over 2 years
        return [
            'starts_at' => 'required|date|after:' . $minDate . '|before:' . $maxDate,
            'duration' => 'required|numeric|min:1',
            'client_id' => 'required|exists:clients,id',
            'caregiver_id' => 'nullable|exists:caregivers,id',
            'fixed_rates' => 'required|boolean',
            'caregiver_rate' => 'nullable|numeric|min:0|max:999.99',
            'client_rate' => 'nullable|numeric|min:0|max:999.99',
//            'provider_fee' => 'nullable|numeric|min:0|max:999.99',
            'caregiver_rate_id' => 'nullable|exists:rate_codes,id',
            'client_rate_id' => 'nullable|exists:rate_codes,id',
            'notes' => 'nullable|string|max:1024',
            'hours_type' => 'required|in:default,overtime,holiday',
//            'overtime_duration' => 'nullable|numeric|min:0|max:' . (int) $this->input('duration'),
            'interval_type' => 'nullable|in:weekly,biweekly,monthly,bimonthly',
            'recurring_end_date' => 'nullable|date|after:' . $this->input('starts_at') . '|before:' . $maxDate,
            'bydays' => 'required_if:interval_type,weekly,biweekly|array',
            'care_plan_id' => 'nullable|exists:care_plans,id',
            'service_id' => 'nullable|exists:services,id',
            'quickbooks_service_id' => ['nullable',
                Rule::exists('quickbooks_services', 'id')->where(function ($query) {
                    $query->where('business_id', $this->getBusinessId());
                }),
            ],
            'payer_id' => [
                'nullable',
                new ValidEffectivePayer($this->client, Carbon::parse($this->input('starts_at')))
            ],
            'services' => 'array|required_without:service_id',
            'services.*.id' => 'nullable|exists:schedule_services,id',
            'services.*.service_id' => 'required_with:services|exists:services,id',
            'services.*.payer_id' => [
                'nullable',
                new ValidEffectivePayer($this->client, Carbon::parse($this->input('starts_at')))
            ],
            'services.*.hours_type' => 'required_with:services|string|in:default,overtime,holiday',
            'services.*.duration' => 'required_with:services|numeric|min:0|max:999.99',
            'services.*.client_rate' => 'nullable|numeric|min:0|max:999.99',
            'services.*.caregiver_rate' => 'nullable|numeric|min:0|max:999.99', // add any other schedule service fields to getServices below
            'services.*.quickbooks_service_id' => ['nullable',
                Rule::exists('quickbooks_services', 'id')->where(function ($query) {
                    $query->where('business_id', $this->getBusinessId());
                }),
            ],
            'status' => 'required|in:' . join(',', [Schedule::OK, Schedule::ATTENTION_REQUIRED, Schedule::CAREGIVER_CANCELED, Schedule::CLIENT_CANCELED, Schedule::CAREGIVER_NOSHOW, Schedule::OPEN_SHIFT, Schedule::HOSPITAL_HOLD]),
        ];
    }

    public function messages()
    {
        return [
            'bydays.required_if' => 'At least one day of the week is required.',
            'recurring_end_date.required_if' => 'You must select an end date for a recurring schedule.',
            'recurring_end_date.max' => 'Schedules can are restricted to a 2 year range.  Lower your recurring end date.',
            'recurring_end_date.min' => 'Your recurring end date cannot be before the start date.',
            'starts_at.min' => 'You cannot create schedule entries prior to 2018.',
            'starts_at.max' => 'Schedules can are restricted to a 2 year range.  Lower your start date.',
            'overtime_duration.max' => 'Overtime duration can not exceed schedule duration.',
            'fixed_rates.*' => 'You must select whether the shift is hourly or daily.',
        ];
    }

    public function getScheduleData(): array
    {
        $data = Arr::except($this->validated(), ['services', 'notes', 'group_update']);

        // unset caregiver on certain statuses
        if (in_array($data['status'], [Schedule::CAREGIVER_CANCELED, Schedule::OPEN_SHIFT])) {
            $data['caregiver_id'] = null;
        }

        return $data;
    }

    public function getNotes(): string
    {
        return trim($this->input('notes', ''));
    }

    public function getServices(): array
    {
        return array_map(function($service) {
            return Arr::only($service, ['id', 'service_id', 'payer_id', 'hours_type', 'duration', 'client_rate', 'caregiver_rate', 'quickbooks_service_id']);
        }, $this->validated()['services'] ?? []);
    }
}
