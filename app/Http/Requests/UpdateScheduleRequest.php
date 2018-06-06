<?php
namespace App\Http\Requests;

use App\Business;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules() {
        $minDate = Carbon::now()->setTime(0, 0, 0);
        $maxDate = Carbon::now()->addDays(735); // A little over 2 years
        return [
            'starts_at' => 'required|integer|min:' . $minDate->getTimestamp() . '|max:' . $maxDate->getTimestamp(),
            'duration' => 'required|numeric|min:1',
            'client_id' => 'required|exists:clients,id',
            'caregiver_id' => 'nullable|exists:caregivers,id',
            'caregiver_rate' => 'required_with:caregiver_id|nullable|numeric',
            'provider_fee' => 'required_with:caregiver_id|nullable|numeric',
            'notes' => 'nullable|max:1024',
            'hours_type' => 'required|in:default,overtime,holiday',
            'overtime_duration' => 'nullable|numeric|min:0|max:' . (int) $this->input('duration'),
            'care_plan_id' => 'nullable|exists:care_plans,id',
        ];
    }

    public function messages()
    {
        return [
            'starts_at.min' => 'You cannot edit past schedules.  The starting date must be today or later.',
            'starts_at.max' => 'Schedules can are restricted to a 2 year range.  Lower your start date.',
            'overtime_duration.max' => 'Overtime duration can not exceed schedule duration.'
        ];
    }
}
