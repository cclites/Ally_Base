<?php
namespace App\Http\Requests;

use Illuminate\Support\Arr;

class CaregiverTimesheetRequest extends CreateTimesheetRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return Arr::except(parent::rules(), [
            'caregiver_id',
            'entries.*.client_rate',
            'entries.*.caregiver_rate'
        ]) + [
            'caregiver_id' => 'required|in:' . \Auth::id(),
            'entries.*.client_rate' => 'nullable|in:null,' . mt_rand(),
            'entries.*.caregiver_rate' => 'nullable|in:null,' . mt_rand(), // must be null
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
            'caregiver_id.in' => 'You are not authorized to submit a timesheet on someone else\'s behalf.',
            'entries.*.client_rate.*' => 'Rates are not allowed.',
            'entries.*.caregiver_rate.*' => 'Rates are not allowed.',
        ] + parent::messages();
    }

}
