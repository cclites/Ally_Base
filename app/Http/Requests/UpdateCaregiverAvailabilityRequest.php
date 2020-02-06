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
        \Log::info( gettype($this->available_start_time) );
        \Log::info($this->all());


        return [
            // CaregiverAvailability
            'monday' => 'required|boolean',
            'tuesday' => 'required|boolean',
            'wednesday' => 'required|boolean',
            'thursday' => 'required|boolean',
            'friday' => 'required|boolean',
            'saturday' => 'required|boolean',
            'sunday' => 'required|boolean',
            'available_start_time' => 'nullable|string',
            'available_end_time' => 'nullable|string',
            'morning' => 'required|boolean',
            'afternoon' => 'required|boolean',
            'evening' => 'required|boolean',
            'night' => 'required|boolean',
            'live_in' => 'required|boolean',
            'minimum_shift_hours' => 'required|numeric|min:0|max:' . $this->input('maximum_shift_hours'),
            'maximum_shift_hours' => 'required|numeric|min:1',
            'maximum_miles' => 'required|numeric|min:1',
            // CaregiverDayOff
            'days_off' => 'nullable|array',
            'days_off.*.start_date' => 'required|date',
            'days_off.*.end_date' => 'required|date',
            'days_off.*.description' => 'required|string|max:156',
            // Caregiver->preferences
            'preferences' => 'nullable|string|max:60000',
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

    /**
     * Get the validated Caregiver's preferences field.
     *
     * @return string|null
     */
    public function preferencesData() : ?string
    {
        $data = $this->validated();
        return isset($data['preferences']) ? $data['preferences'] : null;
    }

    /**
     * Get the data from the request used to create
     * the CaregiverDayOff entries.
     *
     * @return array
     */
    public function daysOffData() : array
    {
        $data = $this->validated();
        return $data['days_off'];
    }

    /**
     * Get the data from the request used to update the
     * CaregiverAvailability record.
     *
     * @return array
     */
    public function availabilityData() : array
    {
        return collect($this->validated())
            ->only([
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'saturday',
                'sunday',
                'morning',
                'afternoon',
                'evening',
                'night',
                'live_in',
                'minimum_shift_hours',
                'maximum_shift_hours',
                'maximum_miles',
                'available_start_time',
                'available_end_time',
            ])
            ->merge(['updated_by' => auth()->id()])
            ->toArray();
    }
}
