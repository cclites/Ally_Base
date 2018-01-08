<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidSSN;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
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
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'username' => ['required', 'email', Rule::unique('users')->ignore($this->client->id)],
            'date_of_birth' => 'nullable|date',
            'business_fee' => 'nullable|numeric',
            'client_type' => 'required',
            'ssn' => ['nullable', new ValidSSN()],
            'gender' => 'nullable|string',
            'onboard_status' => 'required',
            'inquiry_date' => 'nullable|date',
            'service_start_date' => 'nullable|date',
            'referral' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'ambulatory' => 'nullable|boolean'
        ];
    }
}
