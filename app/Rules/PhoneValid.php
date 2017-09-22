<?php

namespace App\Rules;

class PhoneValid extends PhonePossible
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $number = $this->phoneNumberUtil->parse($value, PhoneNumber::DEFAULT_REGION);
        return $this->phoneNumberUtil->isValidNumber($number);
    }
}
