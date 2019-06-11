<?php

namespace App\Http\Requests;

use App\Rules\DistinctArray;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomFieldRequest extends FormRequest
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
            'label' => 'required|string',
            'type' => 'required|string|in:dropdown,radio,input,textarea',
            'user_type' => 'required|string|in:client,caregiver',
            'required' => 'required|boolean',
            'default_value' => 'required_if:required,1',
            'options' => ['required_if:type,dropdown', 'array', new DistinctArray(false)],
            'options.*' => 'string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'default_value' => 'You must enter a default value for required fields.',
            'options.*' => 'You must add at least one option for a list field.',
        ];
    }

    /**
     * Filter the request data for processing.
     *
     * @return array
     */
    public function filtered()
    {
        return $this->validated();
    }
}
