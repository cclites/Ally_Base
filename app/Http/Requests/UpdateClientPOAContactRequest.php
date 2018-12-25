<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientPOAContactRequest extends BusinessRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'poa_first_name' => 'nullable|string|max:50',
            'poa_last_name' => 'nullable|string|max:50',
            'poa_phone' => 'nullable|string|max:25',
            'poa_email' => 'nullable|string|max:128',
            'poa_relationship' => 'nullable|string|max:50',
            'dr_first_name' => 'nullable|string|max:50',
            'dr_last_name' => 'nullable|string|max:50',
            'dr_phone' => 'nullable|string|max:25',
            'dr_fax' => 'nullable|string|max:25',
        ];
    }
}
