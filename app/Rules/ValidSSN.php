<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidSSN implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($allowMasked = true)
    {
        $this->allowMaskedInput = $allowMasked;
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
        if ($value == '123-45-6789') {
            return false;
        }

        if ($this->allowMaskedInput) {
            $pattern = '/(\d{3}|\*{3})-(\d{2}|\*{2})-(\d{4}|\*{4})/';
        } else {
            $pattern = '/^(?!666|000|9\d{2})\d{3}[- ]{0,1}(?!00)\d{2}[- ]{0,1}(?!0{4})\d{4}$/';
        }

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
