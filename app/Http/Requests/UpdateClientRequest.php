<?php

namespace App\Http\Requests;

use App\DisasterCode;
use App\Ethnicity;
use App\Rules\ValidEnum;
use App\StatusAlias;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidSSN;
use Illuminate\Validation\Rule;
use App\Rules\Avatar;
use App\Client;
use When\Valid;

class UpdateClientRequest extends BusinessRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $client = $this->route('client');
        $aliases = StatusAlias::forAuthorizedChain()->forClients()->pluck('id')->toArray();

        return [
            'firstname' => 'required|string|max:45',
            'lastname' => 'required|string|max:45',
            'email' => 'required_unless:no_email,1|nullable|email',
            'username' => ['required_unless:no_username,1', 'nullable', Rule::unique('users')->ignore($client->id)],
            'date_of_birth' => 'nullable|date',
            'business_fee' => 'nullable|numeric',
            'client_type' => 'required',
            'case_manager_id' => 'nullable',
            'ssn' => ['nullable'],
            //'ssn' => ['nullable', new ValidSSN()],
            'gender' => 'nullable|in:M,F',
            'agreement_status' => 'required',
            'inquiry_date' => 'nullable|date',
            'service_start_date' => 'nullable|date',
            'diagnosis' => 'nullable|string|max:100',
            'ambulatory' => 'nullable|boolean',
            'hospital_name' => 'nullable|string|max:100',
            'hospital_number' => 'nullable|string|max:25',
            'avatar' => ['nullable', new Avatar()],
            'referral_source_id' => 'nullable|exists:referral_sources,id',
            'hic' => 'nullable|string|max:50',
            'travel_directions' => 'nullable|string|max:65535',
            'disaster_code_plan' => ['nullable', new ValidEnum(DisasterCode::class)],
            'disaster_planning' => 'nullable|string|max:65535',
            'caregiver_1099' => 'nullable|string|in:ally,client',
            'receive_summary_email' => 'boolean',
            'sales_person_id' => 'nullable|int',
            'status_alias_id' => 'nullable|in:' . join(',', $aliases),
        ];
    }

    public function messages()
    {
        return [
            'email.required_unless' => 'The email is required unless you check the "No Email" box.',
            'username.unique' => 'This username is taken. Please use a different one.',
        ];
    }

    public function filtered()
    {
        $data = $this->validated();
        if ($data['date_of_birth']) $data['date_of_birth'] = filter_date($data['date_of_birth']);
        if ($data['inquiry_date']) $data['inquiry_date'] = filter_date($data['inquiry_date']);
        if ($data['service_start_date']) $data['service_start_date'] = filter_date($data['service_start_date']);
        if (substr($data['ssn'], 0, 3) == '***') unset($data['ssn']);
        if ($this->input('no_email')) $data['email'] = $this->route('client')->getAutoEmail();
        if ($this->input('no_username')) {
            // no need to change the username every time the client is saved
            if ($this->route('client')->hasNoUsername()) {
                $data['username'] = $this->route('client')->username;
            } else {
                $data['username'] = Client::getAutoUsername();
            }
        }
        $data['updated_by'] = auth()->id();

        return $data;
    }
}
