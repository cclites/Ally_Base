<?php
namespace App\Http\Requests;

use App\Billing\Payer;
use App\Billing\Service;
use App\Caregiver;
use App\Client;
use App\Rules\ValidEffectivePayer;
use App\Data\ScheduledRates;
use App\Shift;
use App\Shifts\Data\CaregiverClockoutData;
use App\Shifts\Data\ClockData;
use App\Shifts\ShiftFactory;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class UpdateShiftRequest extends BusinessClientRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'caregiver_id' => 'required|exists:caregivers,id',
            'caregiver_comments' => 'nullable',
            'mileage' => 'nullable|numeric|max:1000|min:0',
            'other_expenses' => 'nullable|numeric|max:1000|min:0',
            'other_expenses_desc' => 'nullable',
            'checked_in_time' => 'required|date',
            'checked_out_time' => 'required|date|after_or_equal:' . $this->input('checked_in_time'),
            'fixed_rates' => 'required|boolean',
            'client_rate' => 'nullable|required_with:caregiver_rate|numeric|max:1000|min:0',
            'caregiver_rate' => 'nullable|required_with:client_rate|numeric|min:0|max:' . $this->input('client_rate') ?? "0",
            'hours_type' => 'required|in:default,overtime,holiday',
            'service_id' => 'nullable|exists:services,id',
            'payer_id' => [
                'nullable',
                new ValidEffectivePayer($this->client, Carbon::parse($this->input('checked_in_time')))
            ],
            'quickbooks_service_id' => ['nullable',
                Rule::exists('quickbooks_services', 'id')->where(function ($query) {
                    $query->where('business_id', $this->getBusinessId());
                }),
            ],
            'issues.id' => 'nullable|numeric',
            'issues.caregiver_injury' => 'boolean',
            'issues.client_injury' => 'boolean',
            'issues.comments' => 'nullable',
            'activities' => 'array|nullable',
            'goals' => 'array|nullable',
            'services' => 'array|required_without:service_id',
            'services.*.id' => 'nullable|exists:shift_services,id',
            'services.*.service_id' => 'required_with:services|exists:services,id',
            'services.*.payer_id' => [
                'nullable',
                new ValidEffectivePayer($this->client, Carbon::parse($this->input('checked_in_time')))
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
        ];
    }

    public function messages()
    {
        return [
            'checked_out_time.after_or_equal' => 'The clock out time cannot be less than the clock in time.',
            'fixed_rates.*' => 'Please select a shift type of hourly or daily.',
            'caregiver_rate.max' => 'The caregiver rate cannot be greater than the total rate.',
            'client_rate.*' => 'The total rate is required and must be a number.',
            'services.*.client_rate.*' => 'The total rate is required and must be a number.',
        ];
    }

    public function filtered()
    {
        $data = $this->validated();
        $data['checked_in_time'] = utc_date($data['checked_in_time'], 'Y-m-d H:i:s', null);
        $data['checked_out_time'] = utc_date($data['checked_out_time'], 'Y-m-d H:i:s', null);
        $data['mileage'] = $this->input('mileage', 0);
        $data['other_expenses'] = $this->input('other_expenses', 0);

        return Arr::except($data, ['issues', 'activities']);
    }

    public function getIssues()
    {
        return $this->validated()['issues'] ?? [];
    }

    public function getActivities()
    {
        return $this->validated()['activities'] ?? [];
    }

    public function getGoals()
    {
        return $this->validated()['goals'] ?? [];
    }

    public function getServices(): array
    {
        return array_map(function($service) {
            return Arr::only($service, ['id', 'service_id', 'payer_id', 'hours_type', 'duration', 'client_rate', 'caregiver_rate', 'quickbooks_service_id']);
        }, $this->validated()['services'] ?? []);
    }

    public function getShiftArray(string $status, string $clockInMethod = Shift::METHOD_OFFICE, $clockOutMethod = null): array
    {
        return $this->getShiftFactory($status, $clockInMethod, $clockOutMethod)->toArray();
    }

    public function getShiftFactory(string $status, string $clockInMethod = Shift::METHOD_OFFICE, $clockOutMethod = null): ShiftFactory
    {
        $checkedInTime = Carbon::parse($this->input('checked_in_time'), $this->getClient()->getTimezone())
            ->setTimezone('UTC')
            ->toDateTimeString();
        $checkedOutTime = Carbon::parse($this->input('checked_out_time'), $this->getClient()->getTimezone())
            ->setTimezone('UTC')
            ->toDateTimeString();

        $clockIn = new ClockData($clockInMethod, $checkedInTime);
        $clockOut = new ClockData($clockOutMethod ?? $clockInMethod, $checkedOutTime);
        $rates = new ScheduledRates(
            $this->input('client_rate'),
            $this->input('caregiver_rate'),
            $this->input('fixed_rates') ?? false,
            $this->input('hours_type') ?? 'default'
        );

        $clockOutData = new CaregiverClockoutData(
            $clockOut,
            $this->input('mileage') ?? 0.0,
            $this->input('other_expenses') ?? 0.0,
            $this->input('other_expenses_desc'),
            $this->input('caregiver_comments')
        );

        $shiftData = ShiftFactory::withoutSchedule(
            $this->getClient(),
            Caregiver::findOrFail($this->input('caregiver_id')),
            $clockIn,
            $clockOut,
            $rates,
            $status,
            $this->input('service_id') ? Service::find($this->input('service_id')) : null,
            $this->input('payer') ? Payer::find($this->input('payer')) : null
        )->withData($clockOutData)->withServices($this->getServices());

        return $shiftData;
    }

    public function createShift(string $status, string $clockInMethod = Shift::METHOD_OFFICE, $clockOutMethod = null): Shift
    {
        $shiftFactory = $this->getShiftFactory($status, $clockInMethod, $clockOutMethod);
        return $shiftFactory->create();
    }
}
