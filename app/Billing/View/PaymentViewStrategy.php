<?php
namespace App\Billing\View;

use App\Billing\Payment;
use App\Contracts\ContactableInterface;

interface PaymentViewStrategy
{
    /**
     * @param \App\Contracts\ContactableInterface $payer
     * @param \App\Billing\Payment $payment
     * @param \App\Billing\View\Data\PaymentInvoiceData[] $invoiceObjects
     * @return mixed
     */
    function generate(ContactableInterface $payer, Payment $payment, array $invoiceObjects);
}