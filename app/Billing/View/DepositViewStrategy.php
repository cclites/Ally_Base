<?php


namespace App\Billing\View;


use App\Billing\Deposit;
use App\Contracts\ContactableInterface;

interface DepositViewStrategy
{
    /**
     * @param \App\Contracts\ContactableInterface $recipient
     * @param \App\Billing\Deposit $deposit
     * @param \App\Billing\View\Data\DepositInvoiceData[] $invoiceObjects
     * @return mixed
     */
    function generate(ContactableInterface $recipient, Deposit $deposit, array $invoiceObjects);
}