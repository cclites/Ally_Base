<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidSSN;
use Illuminate\Validation\Rule;
use App\Rules\Avatar;

class UpdateClientRequest extends BusinessRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required_unless:no_email,1|nullable|email',
            'username' => ['required', Rule::unique('users')->ignore($this->route('client')->id)],
            'date_of_birth' => 'nullable|date',
            'business_fee' => 'nullable|numeric',
            'client_type' => 'required',
            'ssn' => ['nullable', new ValidSSN()],
            'gender' => 'nullable|in:M,F',
            'onboard_status' => 'required',
            'inquiry_date' => 'nullable|date',
            'service_start_date' => 'nullable|date',
            'diagnosis' => 'nullable|string',
            'ambulatory' => 'nullable|boolean',
            'poa_first_name' => 'nullable|string',
            'poa_last_name' => 'nullable|string',
            'poa_phone' => 'nullable|string',
            'poa_relationship' => 'nullable|string',
            'dr_first_name' => 'nullable|string',
            'dr_last_name' => 'nullable|string',
            'dr_phone' => 'nullable|string',
            'dr_fax' => 'nullable|string',
            'hospital_name' => 'nullable|string',
            'hospital_number' => 'nullable|string',
            'avatar' => ['nullable', new Avatar()],
            'referral_source_id' => 'nullable|numeric',
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

        return $data;
    }
}
