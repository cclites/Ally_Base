<?php

namespace App\Rules;

use App\Activity;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;

class ValidActivityCode implements Rule
{
    /**
     * @var
     */
    protected $business_id;

    /**
     * @var null|int
     */
    protected $existing_id;

    /**
     * @var string
     */
    protected $message;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($business_id, $existing_id=null)
    {
        $this->business_id = $business_id;
        $this->existing_id = $existing_id;
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
        if (substr($value, 0, 1) == '0') {
            $this->message = 'Codes starting with 0 are reserved by our system.';
            return false;
        }

        $query = Activity::where(function($q) {
            $q->whereBusinessId($this->business_id)
                ->orWhereNull('business_id');
        });

        if ($this->existing_id) {
            $query->where('id', '!=', $this->existing_id);
        }

        if ($query->where('code', $value)->exists()) {
            $this->message = 'The code must be unique, a code of ' . $value . ' already exists.';
            return false;
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
        return $this->message;
    }
}
