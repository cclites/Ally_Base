<?php


namespace App\Exceptions;


class MissingTimezoneException extends \Exception
{
    protected $message = 'This provider does not have a timezone associated with it. Scheduling functionality cannot continue.';

}