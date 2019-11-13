<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidSSN implements Rule
{
    protected $usesEin = false;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($usesEin = false)
    {
        $this->usesEin = $usesEin;
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
        $pattern = '/(\d{3}|\*{3})-(\d{2}|\*{2})-(\d{4}|\*{4})/';
        return preg_match($pattern, $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid format for the social security number. Expecting ###-##-####';
    }
}
