<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTimesheetsRequest extends FormRequest
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
            'caregiver_id' => 'required|exists:caregivers,id',
            'client_id' => 'required|exists:clients,id',
            'shifts' => 'required|array|min:1',
            
            'shifts.*.mileage' => 'nullable|numeric|max:1000|min:0',
            'shifts.*.other_expenses' => 'nullable|numeric|max:1000|min:0',
            'shifts.*.date' => 'required|date',
            'shifts.*.start_time' => 'required|date_format:H:i',
            'shifts.*.end_time' => 'required|date_format:H:i',
            'shifts.*.caregiver_rate' => 'required|numeric|max:1000|min:0',
            'shifts.*.provider_fee' => 'required|numeric|max:1000|min:0',
            'shifts.*.activities' => 'required|array|min:1',
            'shifts.*.caregiver_comments' => 'nullable',
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
            'caregiver_id.*' => 'You must select a caregiver.',
            'client_id.*' => 'You must select a client.',
            'shifts.*.*' => 'One of the shifts contains invalid data.',
            'shifts.*' => 'You must add at least one shift.',
            'activities.*' => 'You must select at least 1 activity.',
        ];
    }
}
