<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidStartDate implements Rule
{

    /**
     * @var string
     */
    private $startDate;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!$value || !count($value)) return true; // ignore validation for empty bydays (rely on required_if)
        $bydays     = array_map('strtolower', $value);
        $daysOfWeek = ['su', 'mo', 'tu', 'we', 'th', 'fr', 'sa'];
        $weekdayNo  = (int)(new \DateTime($this->startDate))->format('w');
        $weekdayId  = $daysOfWeek[$weekdayNo];
        return (in_array($weekdayId, $bydays));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The start date must be on a day of the week selected in your recurring schedule.';
    }
}
