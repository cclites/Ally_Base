<?php

namespace App\Billing\Contracts;

use App\Billing\ClientInvoice;
use Carbon\Carbon;

interface PaymentInterface
{
    /**
     * Get the date of the payment.
     *
     * @return Carbon
     */
    public function getDate(): Carbon;

    /**
     * Get the type or display name of the payment.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Get the total amount of the payment.
     *
     * @return float
     */
    public function getAmount(): float;

    /**
     * Get the amount applied towards the current invoice.
     *
     * @param ClientInvoice $invoice
     * @return float
     */
    public function getAmountAppliedTowardsInvoice(ClientInvoice $invoice) : float;

    /**
     * Get the payment notes, if they exist.
     * @return string|null
     */
    public function getNotes(): ?string;
}