<?php
namespace App\Exceptions;

class MaximumWeeklyHoursExceeded extends \Exception
{
    public function getStatusCode()
    {
        return 449;
    }

}