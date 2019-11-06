<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateClientMedicationRequest extends FormRequest
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
            'type' => 'required|string',
            'dose' => 'required|string',
            'frequency' => 'required|string',
            'description' => 'required|string',
            'side_effects' => 'nullable|string',
            'notes' => 'nullable|string',
            'tracking' => 'nullable|string',
            'route' => 'nullable|string',
            'was_changed' => 'nullable|boolean',
        ];
    }
}
