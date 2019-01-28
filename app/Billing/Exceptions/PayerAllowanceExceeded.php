<?php
namespace App\Billing\Exceptions;

/**
 * Class PayerAllowanceExceeded
 * This exception is thrown when an invoiceable item cannot be billed to a payer because their allowance would be exceeded
 *
 * @package App\Billing\Exceptions
 */
class PayerAllowanceExceeded extends \Exception
{

}