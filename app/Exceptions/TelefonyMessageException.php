<?php

namespace App\Exceptions;

/**
 * Class TelefonyMessageException
 * @package App\Exceptions
 *
 * Class is used in telefony system to stop processing and respond to the end user with a message.
 * The class is listened for in App\Exceptions\Handler::render
 */
class TelefonyMessageException extends \Exception
{
}
