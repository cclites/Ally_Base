<?php
namespace App\Http\Requests;

use Illuminate\Support\Arr;

class UpdateShiftRequest extends BusinessClientRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'caregiver_id' => 'required|exists:caregivers,id',
            'caregiver_comments' => 'nullable',
            'mileage' => 'nullable|numeric|max:1000|min:0',
            'other_expenses' => 'nullable|numeric|max:1000|min:0',
            'other_expenses_desc' => 'nullable',
            'checked_in_time' => 'required|date',
            'checked_out_time' => 'required|date|after_or_equal:' . $this->input('checked_in_time'),
            'fixed_rates' => 'required|boolean',
            'caregiver_rate' => 'required|numeric|max:1000|min:0',
            'provider_fee' => 'required|numeric|max:1000|min:0',
            'hours_type' => 'required|in:default,overtime,holiday',
            'issues.id' => 'nullable|numeric',
            'issues.caregiver_injury' => 'boolean',
            'issues.client_injury' => 'boolean',
            'issues.comments' => 'nullable',
            'activities' => 'array|nullable',
        ];
    }

    public function messages()
    {
        return [
            'checked_out_time.after_or_equal' => 'The clock out time cannot be less than the clock in time.',
            'fixed_rates.*' => 'Please select a shift type of hourly or daily.',
        ];
    }

    public function filtered()
    {
        $data = $this->validated();
        $data['checked_in_time'] = utc_date($data['checked_in_time'], 'Y-m-d H:i:s', null);
        $data['checked_out_time'] = utc_date($data['checked_out_time'], 'Y-m-d H:i:s', null);
        $data['mileage'] = $this->input('mileage', 0);
        $data['other_expenses'] = $this->input('other_expenses', 0);

        return Arr::except($data, ['issues', 'activities']);
    }

    public function getIssues()
    {
        return $this->validated()['issues'] ?? [];
    }

    public function getActivities()
    {
        return $this->validated()['activities'] ?? [];
    }
}
