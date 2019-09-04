<?php

namespace App\Http\Requests;

use App\ClaimableExpense;
use App\ClaimableService;
use App\Rules\ValidSSN;
use App\Services\GeocodeManager;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Packages\GMaps\GeocodeCoordinates;

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
        return [
            'name' => 'required_if:claimable_type,'.ClaimableExpense::class.'|string',
            'date' => 'required_if:claimable_type,'.ClaimableExpense::class.'|date',
            'notes' => 'nullable|string',

            'rate' => 'required|numeric|min:0|max:999.99',
            'units' => 'required|numeric|min:0|max:999.99',

//            'shift_id' => 'required_if:claimable_type,'.ClaimableService::class.'',
//            'caregiver_id' => 'required_if:claimable_type,'.ClaimableService::class.'',
            'caregiver_first_name' => 'required_if:claimable_type,'.ClaimableService::class.'',
            'caregiver_last_name' => 'required_if:claimable_type,'.ClaimableService::class.'',
            'caregiver_gender' => 'nullable|in:F,M',
            'caregiver_dob' => 'nullable|date',
            'caregiver_ssn' => ['nullable', new ValidSSN()],
            'caregiver_medicaid_id' => 'nullable|string',

            'address1' => 'nullable|string',
            'address2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip' => 'nullable|string',
//            'latitude' => 'required_if:claimable_type,'.ClaimableService::class.'',
//            'longitude' => 'required_if:claimable_type,'.ClaimableService::class.'',

//            'scheduled_start_time' => 'required_if:claimable_type,'.ClaimableService::class.'',
//            'scheduled_end_time' => 'required_if:claimable_type,'.ClaimableService::class.'',
//            'visit_start_time' => 'required_if:claimable_type,'.ClaimableService::class.'',
//            'visit_end_time' => 'required_if:claimable_type,'.ClaimableService::class.'',
//            'evv_start_time' => 'required_if:claimable_type,'.ClaimableService::class.'',
//            'evv_end_time' => 'required_if:claimable_type,'.ClaimableService::class.'',

//            'checked_in_number' => 'required_if:claimable_type,'.ClaimableService::class.'',
//            'checked_out_number' => 'required_if:claimable_type,'.ClaimableService::class.'',
//            'checked_in_latitude' => 'required_if:claimable_type,'.ClaimableService::class.'',
//            'checked_in_longitude' => 'required_if:claimable_type,'.ClaimableService::class.'',
//            'checked_out_latitude' => 'required_if:claimable_type,'.ClaimableService::class.'',
//            'checked_out_longitude' => 'required_if:claimable_type,'.ClaimableService::class.'',
//            'evv_method_in' => 'required_if:claimable_type,'.ClaimableService::class.'',
//            'evv_method_out' => 'required_if:claimable_type,'.ClaimableService::class.'',
//            'service_id' => 'required_if:claimable_type,'.ClaimableService::class.'',
            'service_name' => 'required_if:claimable_type,'.ClaimableService::class.'',
            'service_code' => 'nullable|string',
            'activities' => 'nullable|string',
            'caregiver_comments' => 'nullable|string',

            'shift_start_date' => 'required_if:claimable_type,'.ClaimableService::class.'|date',
            'shift_end_date' => 'required_if:claimable_type,'.ClaimableService::class.'|date',
            'shift_start_time' => 'required_if:claimable_type,'.ClaimableService::class.'',
            'shift_end_time' => 'required_if:claimable_type,'.ClaimableService::class.'',
            'service_start_date' => 'required_if:claimable_type,'.ClaimableService::class.'|date',
            'service_start_time' => 'required_if:claimable_type,'.ClaimableService::class.'',
        ];
    }

    /**
     * Get the data to update the ClaimInvoiceItem's Claimable object.
     *
     * @param string $type
     * @return array
     * @throws ValidationException
     */
    public function getClaimableData(string $type) : array
    {
        $data = collect($this->validated());

        switch ($type) {
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
                $timezone = auth()->user()->officeUser->getTimezone();
                $data['scheduled_start_time'] = Carbon::parse($data['shift_start_date'].' '.$data['shift_start_time'], $timezone)->setTimezone('UTC');
                $data['scheduled_end_time'] = Carbon::parse($data['shift_end_date'].' '.$data['shift_end_time'], $timezone)->setTimezone('UTC');
                $data['visit_start_time'] = Carbon::parse($data['service_start_date'].' '.$data['service_start_time'], $timezone)->setTimezone('UTC');
                $data['visit_end_time'] = $data['visit_start_time']->copy()->addHours($data['units']);

                unset($data['units']);
                unset($data['shift_start_date']);
                unset($data['shift_end_date']);
                unset($data['shift_start_time']);
                unset($data['shift_end_time']);
                unset($data['service_start_date']);
                unset($data['service_start_time']);

                // Geo lookup on address entered
                list($lat, $lon) = $this->lookupGeocode(
                    $this->address1.' '.$this->city.', '.$this->state.' '.$this->country.' '.$this->zip
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
                $data = $data->only(['name', 'notes', 'date'])
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
     * @param string $type
     * @return array
     */
    public function getClaimItemData(string $type) : array
    {
        switch ($type) {
            case ClaimableService::class:
                $data = collect($this->validated())
                    ->only(['rate', 'units', 'service_start_date',])
                    ->toArray();

                $data['date'] = Carbon::parse($data['service_start_date'], $this->getTimezone())->setTimezone('UTC');
                unset($data['service_start_date']);
                break;
            case ClaimableExpense::class:
                $data = collect($this->validated())
                    ->only(['rate', 'units', 'date'])
                    ->toArray();

                $data['date'] = Carbon::parse($data['date'], $this->getTimezone())->setTimezone('UTC');
                break;
            default:
                return [];
        }

        $data['amount'] = multiply(floatval($data['rate']), floatval($data['units']));

        // TODO: Validate amount against the total amount of payments applied towards this item, this value cannot be be less.

        return $data;
    }

    /**
     * Get the geocode for the address
     *
     * @param string|null $fullAddress
     * @return array
     */
    public function lookupGeocode(?string $fullAddress) : array
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
    public function getTimezone() : string
    {
        return auth()->user()->officeUser->getTimezone();
    }
}
