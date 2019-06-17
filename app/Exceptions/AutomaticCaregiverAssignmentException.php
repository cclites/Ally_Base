<?php
namespace App\Exceptions;

class AutomaticCaregiverAssignmentException extends \Exception
{
    public function getStatusCode()
    {
        return 500;
    }
}