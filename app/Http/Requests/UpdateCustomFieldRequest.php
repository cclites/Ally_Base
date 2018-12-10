<?php

namespace App\Http\Requests;

class UpdateCustomFieldRequest extends BusinessRequest
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
            'label' => 'required|string|unique:business_custom_fields',
            'type' => 'required|string|in:dropdown,radio,input,textarea',
            'user_type' => 'required|string|in:client,caregiver',
            'required' => 'required|boolean',
            'default_value' => 'nullable',
        ];
    }
}
