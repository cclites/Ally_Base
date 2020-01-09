<?php

namespace App\Claims\Requests;

use App\Claims\ClaimInvoiceItem;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\GeocodeManager;
use App\Claims\ClaimableService;
use App\Claims\ClaimableExpense;
use Illuminate\Validation\Rule;
use App\Rules\ValidSSN;
use Carbon\Carbon;
use App\Caregiver;
use App\Client;

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

            'caregiver_reload' => 'nullable|boolean',
            'caregiver_id' => ['required',
                Rule::exists('caregivers', 'id')->where(function ($query) {
                    $query->whereIn('id', Caregiver::forAuthorizedChain()->pluck('id'));
                })
            ],
            'caregiver_first_name' => 'required_unless:caregiver_reload,true',
            'caregiver_last_name' => 'required_unless:caregiver_reload,true',
            'caregiver_gender' => 'nullable|in:F,M',
            'caregiver_dob' => 'nullable|date',
            'caregiver_ssn' => ['nullable', new ValidSSN()],
            'caregiver_medicaid_id' => 'nullable|string',

            'address1' => 'required_if:claimable_type,' . ClaimableService::class . '',
            'address2' => 'nullable|string',
            'city' => 'required_if:claimable_type,' . ClaimableService::class . '',
            'state' => 'required_if:claimable_type,' . ClaimableService::class . '',
            'zip' => 'required_if:claimable_type,' . ClaimableService::class . '',

            'service_id' => [
                'required_if:claimable_type,' . ClaimableService::class,
                'nullable',
                Rule::exists('services', 'id')->where(function ($query) {
                    $query->where('chain_id', auth()->user()->officeUser->chain_id);
                })
            ],
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

            // New service data added 12/2019:
            'client_reload' => 'nullable|boolean',
            'client_id' => ['required',
                Rule::exists('clients', 'id')->where(function ($query) {
                    $query->whereIn('id', Client::forRequestedBusinesses()->pluck('id'));
                })
            ],
            'client_first_name' => 'required_unless:client_reload,true',
            'client_last_name' => 'required_unless:client_reload,true',
            'client_dob' => 'nullable|date',
            'client_medicaid_id' => 'nullable',
            'client_medicaid_diagnosis_codes' => 'nullable',
            'client_case_manager' => 'nullable',
            'client_program_number' => 'nullable',
            'client_cirts_number' => 'nullable',
            'client_ltci_policy_number' => 'nullable',
            'client_ltci_claim_number' => 'nullable',
            'client_hic' => 'nullable',
            'client_invoice_notes' => 'nullable',
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
            'service_id.required_if' => 'The :attribute field is required.',
            'name.required_if' => 'The :attribute field is required.',
            'date.required_if' => 'The :attribute field is required.',
            'caregiver_first_name.*' => 'The :attribute field is required.',
            'caregiver_last_name.*' => 'The :attribute field is required.',
            'client_first_name.*' => 'The :attribute field is required.',
            'client_last_name.*' => 'The :attribute field is required.',
            'service_name.required_if' => 'The :attribute field is required.',
            'shift_start_date.required_if' => 'The :attribute field is required.',
            'shift_end_date.required_if' => 'The :attribute field is required.',
            'shift_start_time.required_if' => 'The :attribute field is required.',
            'shift_end_time.required_if' => 'The :attribute field is required.',
            'service_start_date.required_if' => 'The :attribute field is required.',
            'service_start_time.required_if' => 'The :attribute field is required.',
            'address1.required_if' => 'The :attribute field is required.',
            'city.required_if' => 'The :attribute field is required.',
            'state.required_if' => 'The :attribute field is required.',
            'zip.required_if' => 'The :attribute field is required.',
        ];
    }

    /**
     * Get the data to update the ClaimInvoiceItem's Claimable object.
     * NOTE: This method fires an API call to look up addresses which
     * can produce a lag on the system.  This only occurs when the
     * address is detected as changed.
     *
     * @return array
     * @throws ValidationException
     */
    public function getClaimableData(?ClaimInvoiceItem $itemRecord = null): array
    {
        $data = collect($this->validated());

        switch ($data['claimable_type']) {
            case ClaimableService::class:
                $data = $data->only([
                    'units',
                    'address1',
                    'address2',
                    'city',
                    'state',
                    'zip',
                    'service_id',
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

                // convert dates and times
                $timezone = $this->getTimezone();
                $data['scheduled_start_time'] = Carbon::parse($data['shift_start_date'] . ' ' . $data['shift_start_time'], $timezone)->setTimezone('UTC');
                $data['scheduled_end_time'] = Carbon::parse($data['shift_end_date'] . ' ' . $data['shift_end_time'], $timezone)->setTimezone('UTC');
                $data['visit_start_time'] = Carbon::parse($data['service_start_date'] . ' ' . $data['service_start_time'], $timezone)->setTimezone('UTC');
                $data['visit_end_time'] = $data['visit_start_time']->copy()->addHours($data['units']);

                unset($data['units'], $data['shift_start_date'], $data['shift_end_date'], $data['shift_start_time'], $data['shift_end_time'], $data['service_start_date'], $data['service_start_time']);

                // Geo lookup on address entered (only if the service address has changed)
                if ($this->addressHasChangedSince($itemRecord)) {
                    list($lat, $lon) = $this->lookupGeocode(
                        $this->address1 . ' ' . $this->city . ', ' . $this->state . ' ' . $this->zip
                    );

                    if (empty($lat) || empty($lon)) {
                        throw_validation_exception([
                            'address1' => ['This address appears to be invalid.'],
                        ]);
                    }
                    $data['latitude'] = $lat;
                    $data['longitude'] = $lon;
                } else {
                    unset($data['address1']);
                    unset($data['city']);
                    unset($data['state']);
                    unset($data['zip']);
                    unset($data['latitude']);
                    unset($data['longitude']);
                }

                break;

            case ClaimableExpense::class:
                $data = $data->only([
                    'name',
                    'notes',
                    'date',
                ])->toArray();

                $data['date'] = filter_date($data['date']);
                break;
            default:
                return [];
        }

        return $data;
    }

    /**
     * Compare the address given in the request with the specified
     * ClaimInvoiceItem.
     *
     * @param ClaimInvoiceItem|null $item
     * @return bool
     */
    public function addressHasChangedSince(?ClaimInvoiceItem $item) : bool
    {
        if (empty($item)) {
            return true;
        }

        return $item->claimable->address1 != $this->address1 ||
            $item->claimable->city != $this->city ||
            $item->claimable->state != $this->state ||
            $item->claimable->zip != $this->zip;
    }

    /**
     * Get the data to update the ClaimInvoiceItem.
     *
     * @return array
     */
    public function getClaimItemData(): array
    {
        $data = collect($this->validated());

        $itemFields = [
            'caregiver_reload',
            'caregiver_id',
            'caregiver_first_name',
            'caregiver_last_name',
            'caregiver_gender',
            'caregiver_dob',
            'caregiver_ssn',
            'caregiver_medicaid_id',
            'client_reload',
            'client_id',
            'client_first_name',
            'client_last_name',
            'client_dob',
            'client_medicaid_id',
            'client_medicaid_diagnosis_codes',
            'client_case_manager',
            'client_program_number',
            'client_cirts_number',
            'client_ltci_policy_number',
            'client_ltci_claim_number',
            'client_hic',
            'client_invoice_notes',
        ];

        switch ($data['claimable_type']) {
            case ClaimableService::class:
                $data = $data->only(array_merge($itemFields, ['rate', 'units', 'service_start_date']))
                    ->toArray();

                $data['date'] = Carbon::parse($data['service_start_date'], $this->getTimezone())->setTimezone('UTC');
                unset($data['service_start_date']);
                break;

            case ClaimableExpense::class:
                $data = $data->only(array_merge($itemFields, ['rate', 'units', 'date']))
                    ->toArray();

                $data['date'] = Carbon::parse($data['date'], $this->getTimezone())->setTimezone('UTC');
                break;
            default:
                return [];
        }

        $data['amount'] = multiply(floatval($data['rate']), floatval($data['units']));

        if ($data['client_dob']) {
            $data['client_dob'] = filter_date($data['client_dob']);
        }

        if ($data['caregiver_dob']) {
            $data['caregiver_dob'] = filter_date($data['caregiver_dob']);
        }

        // Only update SSN if not masked
        if (substr($data['caregiver_ssn'], 0, 3) == '***') {
            unset($data['caregiver_ssn']);
        }

        if (isset($data['caregiver_reload']) && $data['caregiver_reload']) {
            // Update all caregiver data from the database.
            $caregiver = Caregiver::findOrFail($data['caregiver_id']);
            $data['caregiver_first_name'] = $caregiver->first_name;
            $data['caregiver_last_name'] = $caregiver->last_name;
            $data['caregiver_gender'] = $caregiver->gender;
            $data['caregiver_dob'] = $caregiver->date_of_birth;
            $data['caregiver_ssn'] = $caregiver->ssn;
            $data['caregiver_medicaid_id'] = $caregiver->medicaid_id;
            unset($data['caregiver_reload']);
        }

        if (isset($data['client_reload']) && $data['client_reload']) {
            $client = Client::findOrFail($data['client_id']);
            $data['client_first_name'] = $client->first_name;
            $data['client_last_name'] = $client->last_name;
            $data['client_medicaid_id'] = $client->medicaid_id;
            $data['client_medicaid_diagnosis_codes'] = $client->medicaid_diagnosis_codes;
            $data['client_case_manager'] = $client->case_manager;
            // TODO: how would we know to reload the client payer ?
            // $data['client_program_number'] = $clientPayer->program_number;
            // $data['client_cirts_number'] = $clientPayer->cirts_number;
            // $data['client_invoice_notes'] = $clientPayer->notes;
            $data['client_ltci_policy_number'] = $client->getPolicyNumber();
            $data['client_ltci_claim_number'] = $client->getClaimNumber();
            $data['client_hic'] = $client->hic;
            unset($data['client_reload']);
        }

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
