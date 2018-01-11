<?php
namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateScheduleRequest extends BulkDestroyScheduleRequest
{
    public function rules() {
        $minDate = Carbon::now()->setTime(0, 0, 0);
        $maxDate = Carbon::now()->addYears(2);
        return array_merge(
                parent::rules(),
                [
                    // modification items
                    'new_starts_time' => 'nullable|date_format:H:i:s',
                    'new_duration' => 'required_if:new_starts_at|numeric',
                    'new_provider_fee' => 'nullable|numeric',
                    'new_caregiver_rate' => 'nullable|numeric',
                    'new_notes' => 'nullable|max:1024',
                    'new_hours_type' => 'nullable|in:default,overtime,holiday',
                    // overtime_duration of -1 needs to make the schedule's overtime_duration = duration
                    'new_overtime_duration' => 'nullable|numeric|min:-1|max:' . $this->input('duration'),
//                    'new_interval_type' => 'nullable|in:weekly,biweekly,monthly,bimonthly',
//                    'new_recurring_end_date' => 'required_if:interval_type|date_format:Y-m-d|after:starts_at|max:' . $maxDate,
//                    'new_bydays' => 'required_if:new_interval_type,weekly,biweekly|array',
                ]
        );
    }

    public function messages()
    {
        return array_merge(
            parent::messages(),
            [
                'new_overtime_duration|max' => 'Overtime duration can not exceed schedule duration.'
            ]
        );
    }
}