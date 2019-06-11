<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DistinctArray implements Rule
{
    /**
     * @var bool
     */
    private $caseSensitive = false;

    /**
     * Create a new rule instance.
     *
     * @param bool $caseSensitive
     * @return void
     */
    public function __construct($caseSensitive = false)
    {
        $this->caseSensitive = $caseSensitive;
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
        if (! is_array($value)) {
            return true;
        }

        if ($this->caseSensitive) {
            $unique = array_unique($value);
        } else {
            $unique = array_unique(
                array_map( "strtolower", $value )
            );
        }

        return count($unique) === count($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attribute contains duplicate entries.';
    }
}
