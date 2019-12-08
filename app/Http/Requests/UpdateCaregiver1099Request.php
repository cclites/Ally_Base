<?php

namespace App\Http\Requests;

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
            'client_fname' => 'required|string',
            'client_lname' => 'required|string',
            'client_address1' => 'required|string',
            'client_address2' => 'nullable|string',
            'client_city' => 'required|string',
            'client_state' => 'required|string',
            'client_zip' => 'required|string',
            'caregiver_fname' => 'required|string',
            'caregiver_lname' => 'required|string',
            'caregiver_address1' => 'required|string',
            'caregiver_address2' => 'nullable|string',
            'caregiver_city' => 'required|string',
            'caregiver_state' => 'required|string',
            'caregiver_zip' => 'required|string',
        ];
    }
}
