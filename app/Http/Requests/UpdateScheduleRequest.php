<?php
namespace App\Http\Requests;

use Illuminate\Support\Arr;

class UpdateScheduleRequest extends CreateScheduleRequest
{
    public function rules() {
        return Arr::except(parent::rules(), ['interval_type', 'recurring_end_date', 'bydays'])
            + [
                'group_update' => 'nullable|string|in:single,future_weekday,future_all,total_weekday,total_all',
                'status' => 'sometimes|required|string|min:2',
            ];
    }

    public function messages()
    {
        return parent::messages() + [
            'status' => 'The schedule status must be selected in the notes tab.',
        ];
    }
}
