<?php


namespace App\Events\Contracts;


use App\Billing\Payment;

interface PaymentEvent
{
    public function __construct(Payment $payment);
    public function getPayment(): Payment;
}