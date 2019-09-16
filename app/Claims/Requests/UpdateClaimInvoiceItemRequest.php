<?php

namespace App\Claims\Requests;

use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\GeocodeManager;
use App\Claims\ClaimableService;
use App\Claims\ClaimableExpense;
use App\Rules\ValidSSN;
use Carbon\Carbon;

class UpdateClaimInvoiceItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // We are not validating the amount based on remit applications/adjustments
        // because this is handled when the balance is adjust after the save.
        // See the ClaimInvoiceItemController@update method for more details.

        return [
            'claimable_type' => 'required|in:' . ClaimableExpense::class . ',' . ClaimableService::class,
            'name' => 'required_if:claimable_type,' . ClaimableExpense::class . '',
            'date' => 'required_if:claimable_type,' . ClaimableExpense::class . '|date',
            'notes' => 'nullable|string',

            'rate' => 'required|numeric|min:0|max:999.99',
            'units' => 'required|numeric|min:0|max:999.99',

            'caregiver_first_name' => 'required',
            'caregiver_last_name' => 'required',
            'caregiver_gender' => 'nullable|in:F,M',
            'caregiver_dob' => 'nullable|date',
            'caregiver_ssn' => ['nullable', new ValidSSN()],
            'caregiver_medicaid_id' => 'nullable|string',

            'address1' => 'nullable|string',
            'address2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip' => 'nullable|string',

            'service_name' => 'required_if:claimable_type,' . ClaimableService::class . '',
            'service_code' => 'nullable|string',
            'activities' => 'nullable|string',
            'caregiver_comments' => 'nullable|string',

            'shift_start_date' => 'required_if:claimable_type,' . ClaimableService::class . '|date',
            'shift_end_date' => 'required_if:claimable_type,' . ClaimableService::class . '|date',
            'shift_start_time' => 'required_if:claimable_type,' . ClaimableService::class . '',
            'shift_end_time' => 'required_if:claimable_type,' . ClaimableService::class . '',
            'service_start_date' => 'required_if:claimable_type,' . ClaimableService::class . '|date',
            'service_start_time' => 'required_if:claimable_type,' . ClaimableService::class . '',
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required_if' => 'The :attribute field is required.',
            'date.required_if' => 'The :attribute field is required.',
            'caregiver_first_name.required_if' => 'The :attribute field is required.',
            'caregiver_last_name.required_if' => 'The :attribute field is required.',
            'service_name.required_if' => 'The :attribute field is required.',
            'shift_start_date.required_if' => 'The :attribute field is required.',
            'shift_end_date.required_if' => 'The :attribute field is required.',
            'shift_start_time.required_if' => 'The :attribute field is required.',
            'shift_end_time.required_if' => 'The :attribute field is required.',
            'service_start_date.required_if' => 'The :attribute field is required.',
            'service_start_time.required_if' => 'The :attribute field is required.',
        ];
    }

    /**
     * Get the data to update the ClaimInvoiceItem's Claimable object.
     *
     * @return array
     * @throws ValidationException
     */
    public function getClaimableData(): array
    {
        $data = collect($this->validated());

        switch ($data['claimable_type']) {
            case ClaimableService::class:
                $data = $data->only([
                    'units',
                    'caregiver_first_name',
                    'caregiver_last_name',
                    'caregiver_gender',
                    'caregiver_dob',
                    'caregiver_ssn',
                    'caregiver_medicaid_id',
                    'address1',
                    'address2',
                    'city',
                    'state',
                    'zip',
                    'service_name',
                    'service_code',
                    'activities',
                    'caregiver_comments',

                    'shift_start_date',
                    'shift_end_date',
                    'shift_start_time',
                    'shift_end_time',
                    'service_start_date',
                    'service_start_time',
                ])->toArray();

                if ($data['caregiver_dob']) {
                    $data['caregiver_dob'] = filter_date($data['caregiver_dob']);
                }

                // convert dates and times
                $timezone = $this->getTimezone();
                $data['scheduled_start_time'] = Carbon::parse($data['shift_start_date'] . ' ' . $data['shift_start_time'], $timezone)->setTimezone('UTC');
                $data['scheduled_end_time'] = Carbon::parse($data['shift_end_date'] . ' ' . $data['shift_end_time'], $timezone)->setTimezone('UTC');
                $data['visit_start_time'] = Carbon::parse($data['service_start_date'] . ' ' . $data['service_start_time'], $timezone)->setTimezone('UTC');
                $data['visit_end_time'] = $data['visit_start_time']->copy()->addHours($data['units']);

                unset($data['units'], $data['shift_start_date'], $data['shift_end_date'], $data['shift_start_time'], $data['shift_end_time'], $data['service_start_date'], $data['service_start_time']);

                // Geo lookup on address entered
                list($lat, $lon) = $this->lookupGeocode(
                    $this->address1 . ' ' . $this->city . ', ' . $this->state . ' ' . $this->country . ' ' . $this->zip
                );

                if (empty($lat) || empty($lon)) {
                    throw_validation_exception([
                        'address1' => ['This address appears to be invalid.'],
                    ]);
                }
                $data['latitude'] = $lat;
                $data['longitude'] = $lon;

                // Only update SSN if not masked
                if (substr($data['caregiver_ssn'], 0, 3) == '***') {
                    unset($data['caregiver_ssn']);
                }

                break;
            case ClaimableExpense::class:
                $data = $data->only(['name', 'notes', 'date', 'caregiver_first_name', 'caregiver_last_name'])
                    ->toArray();
                $data['date'] = filter_date($data['date']);
                break;
            default:
                return [];
        }

        return $data;
    }

    /**
     * Get the data to update the ClaimInvoiceItem.
     *
     * @return array
     */
    public function getClaimItemData(): array
    {
        $data = collect($this->validated());

        switch ($data['claimable_type']) {
            case ClaimableService::class:
                $data = $data->only(['rate', 'units', 'service_start_date'])
                    ->toArray();

                $data['date'] = Carbon::parse($data['service_start_date'], $this->getTimezone())->setTimezone('UTC');
                unset($data['service_start_date']);
                break;
            case ClaimableExpense::class:
                $data = $data->only(['rate', 'units', 'date'])
                    ->toArray();

                $data['date'] = Carbon::parse($data['date'], $this->getTimezone())->setTimezone('UTC');
                break;
            default:
                return [];
        }

        $data['amount'] = multiply(floatval($data['rate']), floatval($data['units']));

        return $data;
    }

    /**
     * Get the geocode for the address
     *
     * @param string|null $fullAddress
     * @return array
     */
    public function lookupGeocode(?string $fullAddress): array
    {
        if (empty($fullAddress)) {
            return [null, null];
        }

        try {
            $manager = app(GeocodeManager::class);
            if ($geocode = $manager->getCoordinates($fullAddress)) {
                return [$geocode->latitude, $geocode->longitude];
            }
        } catch (\Packages\GMaps\Exceptions\NoGeocodeFoundException $e) {
        }

        return [null, null];
    }

    /**
     * Get the current user's timezone.
     *
     * @return string
     */
    public function getTimezone(): string
    {
        $timezone = optional(auth()->user()->officeUser)->getTimezone();
        if (empty($timezone)) {
            $timezone = 'America/New_York';
        }
        return $timezone;
    }
}
