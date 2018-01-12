<?php

namespace App\Rules;

use App\Activity;
use App\Client;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;

class SignedLTCI implements Rule
{
    /**
     * @var
     */
    protected $client_type;
    
    /**
     * @var string
     */
    protected $message;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($client_type)
    {
        $this->client_type = $client_type;
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
        if ($this->client_type == "LTCI") {
            return ! empty($value);
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "A :attribute is required for Long Term Care Insurance (LTCI) Clients. 
            Please Add a Signature to proceed. ";
    }
}
