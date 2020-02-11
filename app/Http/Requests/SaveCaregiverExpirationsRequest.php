<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveCaregiverExpirationsRequest extends FormRequest
{

    /**
     * A default authorize() method to return true.  Authorization rules normally belong in a policy, called from the controller.
     *
     * @return bool
     */
    public function authorize()
    {
        return is_office_user() || is_admin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            '*.id' => 'nullable',
            '*.name' => 'required',
            '*.description' => 'nullable|max:100|min:0',
            '*.expires_at' => 'required|date_format:m/d/Y',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [

            '*.name.*' => 'Expirations require a name',
            '*.description.*' => 'Descriptions cannot be longer than 100 characters',
            '*.expires_at.*' => 'A valid expiration date is required'
        ];
    }
}
