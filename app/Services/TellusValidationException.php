<?php

namespace App\Services;

class TellusValidationException extends \Exception
{
    /**
     * @var array
     */
    protected $errors = [];

    function __construct(string $message, array $errors = null)
    {
        $this->message = $message;
        $this->code = 0;
        $this->errors = $errors;
    }

    public function getErrors() : array
    {
        return $this->errors;
    }

    public function hasErrors() : bool
    {
        return count($this->errors) > 0;
    }

}
