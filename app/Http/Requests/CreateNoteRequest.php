<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateNoteRequest extends FormRequest
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
            'business_id' => 'nullable|exists:businesses,id',
            'caregiver_id' => 'nullable|exists:caregivers,id',
            'client_id' => 'nullable|exists:clients,id',
            'tags' => 'nullable|string',
            'body' => 'required|string',
        ];
    }
}
