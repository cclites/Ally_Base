<?php

namespace App\Http\Requests;

use App\Rules\ValidSSN;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCaregiver1099Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if(auth()->user()->role_type === 'admin'){
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'year' => 'required|integer',
            'business_id' => 'required|integer',
            'client_id' => 'required|integer',
            'caregiver_id' => 'required|integer',
            'client_first_name' => 'required|string',
            'client_last_name' => 'required|string',
            'client_address1' => 'required|string',
            'client_address2' => 'nullable|string',
            'client_city' => 'required|string',
            'client_state' => 'required|string',
            'client_zip' => 'required|string',
            'caregiver_first_name' => 'required|string',
            'caregiver_last_name' => 'required|string',
            'caregiver_address1' => 'required|string',
            'caregiver_address2' => 'nullable|string',
            'caregiver_city' => 'required|string',
            'caregiver_state' => 'required|string',
            'caregiver_zip' => 'required|string',
            'payment_total' => 'required',
            'client_ssn' => ['required', new ValidSSN(true)],
            'caregiver_ssn' => ['required', new ValidSSN(true)],
        ];
    }

    /**
     * Filter the request data for processing.
     *
     * @return array
     */
    public function filtered(): array
    {
        $data = $this->validated();

        if (isset($data['client_ssn'])) {
            if (substr($data['client_ssn'], 0, 3) == '***') {
                unset($data['client_ssn']);
            } else {
                $data['client_ssn'] = encrypt($data['client_ssn']);
            }
        }

        if (isset($data['caregiver_ssn'])) {
            if (substr($data['caregiver_ssn'], 0, 3) == '***') {
                unset($data['caregiver_ssn']);
            } else {
                $data['caregiver_ssn'] = encrypt($data['caregiver_ssn']);
            }
        }

        return $data;
    }
}
