<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidEnum implements Rule
{
    /**
     * @var string
     */
    private $enumClass;

    /**
     * Create a new rule instance.
     *
     * @param string $enumClass
     * @return void
     */
    public function __construct(string $enumClass)
    {
        $this->enumClass = $enumClass;
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
        return $this->enumClass::isValid($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid :attribute.';
    }
}
