<?php

namespace App\Http\Requests;

use App\Schedule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class BulkDestroyScheduleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * Build a Schedule query using the request data
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scheduleQuery()
    {
        $query = Schedule::whereBetween('starts_at',
            [
                filter_date($this->start_date) . ' 00:00:00',
                filter_date($this->end_date) . ' 23:59:59',
            ]
        );

        if ($this->start_time) {
            $query->where('starts_at', 'LIKE', '% ' . $this->start_time . '%');
        }

        if ($this->client_id) {
            $query->where('client_id', $this->client_id);
        }

        if ($this->caregiver_id !== null) {
            // Convert 0 to null
            $caregiver_id = $this->caregiver_id ? $this->caregiver_id : null;
            $query->where('caregiver_id', $caregiver_id);
        }

        if ($this->daily_rates !== null) {
            $query->where('daily_rates', $this->daily_rates);
        }

        if ($this->hours_type) {
            $query->where('hours_type', $this->hours_type);
        }

        if ($this->bydays) {
            $query->whereIn('weekday', $this->convertedByDays());
        }

        return $query;
    }

    protected function convertedByDays()
    {
        $days = [
            'su' => 0,
            'mo' => 1,
            'tu' => 2,
            'we' => 3,
            'th' => 4,
            'fr' => 5,
            'sa' => 6,
        ];

        $results = [];
        foreach($this->bydays as $day) {
            $day = strtolower($day);
            if (isset($days[$day])) {
                $results[] = $days[$day];
            }
        }

        return $results;
    }

    public function rules()
    {
        $minDate = Carbon::now()->subHours(8)->setTime(0, 0, 0);
        $maxDate = Carbon::now()->addYears(2);
        return [
            // query items
            'start_date'   => 'required|date|after_or_equal:' . $minDate . '|before:' . $maxDate,
            'end_date'     => 'required|date|after_or_equal:' . $minDate,
            'start_time'   => 'nullable|date_format:H:i',
            'client_id'    => 'nullable|exists:clients,id',
            'caregiver_id' => 'nullable|integer', // cannot use exists rule because 0 is used for unassigned
            'hours_type'   => 'nullable|in:default,overtime,holiday',
            'bydays'       => 'required|array', // match all days if null
        ];
    }

    public function messages()
    {
        return [
            'required_unless.client_id'    => 'Either a client or a caregiver must be selected.',
            'required_unless.caregiver_id' => 'Either a client or a caregiver must be selected.',
            'hours_type.in'                => 'Invalid special designation',
            'bydays.required'              => 'You must select at least one day of the week to match against.',
            'bydays.array'                 => 'You must select at least one day of the week to match against.',
            'client_id.exists'             => 'You must select which client(s) to match against.',
            'caregiver_id.integer'         => 'You must select which caregiver(s) to match against.',
            'daily_rates.*'                => 'You must select a rate structure to match against.',
            'start_time.*'                 => 'Please enter a valid start time.',
        ];
    }
}