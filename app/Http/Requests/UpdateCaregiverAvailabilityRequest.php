<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCaregiverAvailabilityRequest extends FormRequest
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
            'monday' => 'required|boolean',
            'tuesday' => 'required|boolean',
            'wednesday' => 'required|boolean',
            'thursday' => 'required|boolean',
            'friday' => 'required|boolean',
            'saturday' => 'required|boolean',
            'sunday' => 'required|boolean',
            'morning' => 'required|boolean',
            'afternoon' => 'required|boolean',
            'evening' => 'required|boolean',
            'night' => 'required|boolean',
            'live_in' => 'required|boolean',
            'minimum_shift_hours' => 'required|numeric|min:0|max:' . $this->input('maximum_shift_hours'),
            'maximum_shift_hours' => 'required|numeric|min:1',
            'maximum_miles' => 'required|numeric|min:1',
            'days_off' => 'nullable|array',
            'days_off.*.start_date' => 'required|date',
            'days_off.*.end_date' => 'required|date',
            'days_off.*.description' => 'required|string|max:156',
        ];
    }

    public function messages()
    {
        return [
            'minimum_shift_hours.*' => 'Minimum shift hours must be a number less than or equal to maximum shift hours.',
            'maximum_shift_hours.*' => 'Maximum shift hours must be a number greater than 0.',
            'maximum_miles.*' => 'You must select the maximum number of miles.',
            'days_off.*' => 'There is an error with one of the days off.',
        ];
    }
}
