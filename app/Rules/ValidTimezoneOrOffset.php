<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidTimezoneOrOffset implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        try {
            $test = new \DateTimeZone($value);
            return ($test instanceof \DateTimeZone);
        }
        catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The system could not properly determine your timezone (invalid offset).  Please contact support.';
    }
}
