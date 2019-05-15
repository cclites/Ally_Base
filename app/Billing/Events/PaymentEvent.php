<?php
namespace App\Billing\Events;

use App\Billing\Payment;

interface PaymentEvent
{
    public function getPayment(): Payment;
}