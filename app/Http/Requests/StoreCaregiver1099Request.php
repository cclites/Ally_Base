<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCaregiver1099Request extends FormRequest
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
            'year' => 'required|integer',
            'business_id' => 'required|integer',
            'client_id' => 'required|integer',
            'caregiver_id' => 'required|integer',
            'override_payer_to_ally' => 'nullable|boolean',
        ];
    }
}