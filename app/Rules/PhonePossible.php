<?php

namespace App\Rules;

use App\PhoneNumber;
use Illuminate\Contracts\Validation\Rule;
use libphonenumber\PhoneNumberUtil;

class PhonePossible implements Rule
{
    protected $phoneNumberUtil;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
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
            $number = $this->phoneNumberUtil->parse($value, PhoneNumber::DEFAULT_REGION);
            return $this->phoneNumberUtil->isPossibleNumber($number);
        } catch (\Exception $ex) {
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
        return 'The :attribute is not a valid phone number.';
    }
}
