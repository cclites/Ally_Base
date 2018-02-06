<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateScheduleRequest extends BulkDestroyScheduleRequest
{
    protected function updateRules()
    {
        return [
            'new_start_time'        => 'nullable|date_format:H:i',
            'new_duration'          => 'nullable|required_with:new_start_time|numeric',
            'new_caregiver_id'      => 'nullable|integer', // cannot use exists rule because 0 is used for unassigned
            'new_caregiver_rate'    => 'nullable|numeric|min:0|max:1000',
            'new_provider_fee'      => 'nullable|numeric|min:0|max:1000',
            'new_note_method'       => 'nullable|in:append,overwrite',
            'new_note_text'         => 'nullable|max:1024',
            'new_hours_type'        => 'nullable|in:default,overtime,holiday',
            // overtime_duration of -1 needs to make the schedule's overtime_duration = duration
            'new_overtime_duration' => 'nullable|numeric|min:-1|max:' . $this->input('duration'),
        ];
    }

    public function getUpdateData()
    {
        $allowedKeys = array_keys($this->updateRules());

        // Filter out null values and unallowed keys
        $data = array_filter($this->validated(), function($value, $key) use ($allowedKeys) {
             return $value !== null && in_array($key, $allowedKeys);
        }, ARRAY_FILTER_USE_BOTH);

        if (isset($data['new_caregiver_id']) && $data['new_caregiver_id'] == 0) {
            $data['new_caregiver_id'] = null;
        }

        return $data;
    }

    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->updateRules()
        );
    }

    public function messages()
    {
        return array_merge(
            parent::messages(),
            [
                'new_duration.required_with' => 'An end time is required when updating shift times.',
                'new_duration.numeric'       => 'Invalid new end time',
                'new_overtime_duration:max'  => 'Overtime duration can not exceed schedule duration.'
            ]
        );
    }
}