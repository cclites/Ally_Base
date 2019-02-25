<?php
namespace App\Http\Requests;

class CreateTimesheetRequest extends BusinessClientRequest
{

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
            'entries' => 'required|array|min:1',

            'entries.*.mileage' => 'nullable|numeric|max:1000|min:0',
            'entries.*.other_expenses' => 'nullable|numeric|max:1000|min:0',
            'entries.*.checked_in_time' => 'required|date_format:Y-m-d H:i:s',
            'entries.*.checked_out_time' => 'required|date_format:Y-m-d H:i:s',
            'entries.*.client_rate' => 'nullable|numeric|max:1000|min:0',
            'entries.*.caregiver_rate' => 'nullable|numeric|max:1000|min:0',
            'entries.*.activities' => 'required|array|min:1',
            'entries.*.caregiver_comments' => 'nullable',
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
            'entries.*.mileage.*' => 'Mileage cannot be greater than 1000 or less than 0 for a single shift.',
            'entries.*.other_expenses.*' => 'Other expenses cannot be greater than 1000 or less than 0 for a single shift.',
            'entries.*.*' => 'One of the entries contains invalid data.',
            'entries.*' => 'You must add at least one shift.',
            'activities.*' => 'You must select at least 1 activity.',
        ];
    }

}
