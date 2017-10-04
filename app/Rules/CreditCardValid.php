<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Inacho\CreditCard;

class CreditCardValid implements Rule
{
    protected $validCheck;
    protected $typeCheck;

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
        $validator = CreditCard::validCreditCard($value);
        $types = ['visa', 'mastercard', 'discover', 'amex'];
        $this->validCheck = ($validator['valid'] == 1);
        $this->typeCheck = in_array($validator['type'], $types);
        return $this->validCheck && $this->typeCheck;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if (!$this->validCheck) {
            return 'Invalid credit card number.';
        }
        if (!$this->typeCheck) {
            return 'Unsupported credit card type.';
        }
    }
}
