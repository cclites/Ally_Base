<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class BulkDestroyScheduleRequest extends FormRequest
{
    public function rules()
    {
        $minDate = Carbon::now()->setTime(0, 0, 0);
        $maxDate = Carbon::now()->addYears(2);
        return [
            // query items
            'start_date'   => 'required|date_format:Y-m-d|min:' . $minDate . '|max:' . $maxDate,
            'end_date'     => 'required|date_format:Y-m-d|min:' . $minDate . '|max:' . $maxDate,
            'start_time'   => 'required|date_format:H:i:s',
            'client_id'    => 'nullable|exists:clients',
            'caregiver_id' => 'nullable|integer', // cannot use exists rule because 0 is used for unassigned
            'hours_type'   => 'nullable|in:default,overtime,holiday',
            'bydays'       => 'nullable|array', // match all days if null
        ];
    }

    public function messages()
    {
        return [
            'required_unless:client_id'    => 'Either a client or a caregiver must be selected.',
            'required_unless:caregiver_id' => 'Either a client or a caregiver must be selected.',
            'hours_type:in'                => 'Invalid special designation',
        ];
    }
}