<?php
namespace App\Billing\Contracts;

use App\Billing\ClientInvoiceItem;
use App\Billing\Deposit;
use App\Billing\Payment;
use App\Business;
use App\Caregiver;
use App\Client;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface InvoiceableInterface
{
    /**
     * Collect all applicable invoiceables of this type eligible for the client payment
     *
     * @param \App\Client $client
     * @param \Carbon\Carbon $endDateUtc   The last date of service eligible for payment
     * @return \Illuminate\Support\Collection|\App\Billing\Contracts\InvoiceableInterface[]
     */
    public function getItemsForPayment(Client $client, Carbon $endDateUtc): Collection;

    /**
     * Collect all applicable invoiceables of this type eligible for the caregiver deposit
     *
     * @param \App\Caregiver $caregiver
     * @return \Illuminate\Support\Collection|\App\Billing\Contracts\InvoiceableInterface[]
     */
    public function getItemsForCaregiverDeposit(Caregiver $caregiver): Collection;

    /**
     * Collect all applicable invoiceables of this type eligible for the provider deposit
     *
     * @param \App\Business $business
     * @return \Illuminate\Support\Collection|\App\Billing\Contracts\InvoiceableInterface[]
     */
    public function getItemsForBusinessDeposit(Business $business): Collection;

    /**
     * Get a unique hash for this item
     *
     * @return string
     */
    public function getItemHash(): string;

    /**
     * Get the number of units to be invoiced
     *
     * @return float
     */
    public function getItemUnits(): float;

    /**
     * Get the name of this item to display on the invoice
     *
     * @param string $invoiceModel
     * @return string
     */
    public function getItemName(string $invoiceModel): string;

    /**
     * Get the group this item should be listed under on the invoice
     *
     * @param string $invoiceModel
     * @return string|null
     */
    public function getItemGroup(string $invoiceModel): ?string;

    /**
     * Get the date & time that this item's "service" occurred.   SHOULD respect the client/business timezone.
     * Note: This is used for sorting items on the invoice and determining payer allowances.
     *
     * @return string|null
     */
    public function getItemDate(): ?string;

    /**
     * @return string|null
     */
    public function getItemNotes(): ?string;

    /**
     * Check if the client rate includes the ally fee (ex. true for shifts, false for expenses)
     *
     * @return bool
     */
    public function hasFeeIncluded(): bool;

    /**
     * @return \App\Shift|null
     */
    public function getShift(): ?Shift;

    /**
     * @return \App\Client|null
     */
    public function getClient(): ?Client;

    /**
     * Get the client rate of this item (payment rate).  The total charged will be this rate multiplied by the units.
     *
     * @return float
     */
    public function getClientRate(): float;

    /**
     * @return \App\Caregiver|null
     */
    public function getCaregiver(): ?Caregiver;

    /**
     * @return float
     */
    public function getCaregiverRate(): float;

    /**
     * Return the ally fee per unit for this invoiceable item.
     * If this returns null, abort deposit invoices.  Return 0.0 for no ally fee.
     *
     * @return float|null
     */
    public function getAllyRate(): ?float;

    /**
     * @return \App\Business|null
     */
    public function getBusiness(): ?Business;

    /**
     * Note: This is a calculated field from the other rates, provider is synonymous with business.
     * @param null $allyRate
     * @return float
     */
    public function getProviderRate($allyRate = null): float;

    /**
     * Get the assigned payer ID (payers.id, not client_payers.id)
     *
     * @return int|null
     */
    public function getPayerId(): ?int;

    /**
     * Get the amount due for payment, this should subtract the amount invoiced
     *
     * @return float
     */
    public function getAmountDue(): float;

    /**
     * Get the amount that has been invoiced to the client
     *
     * @return float
     */
    public function getAmountInvoiced(): float;

    /**
     * Add an amount that has been invoiced to a payer
     *
     * @param \App\Billing\ClientInvoiceItem $invoiceItem
     * @param float $amount
     * @param float $allyFee  The value of $amount that represents the Ally Fee
     */
    public function addAmountInvoiced(ClientInvoiceItem $invoiceItem, float $amount, float $allyFee): void;

    /**
     * Get the amount that has been charged
     *
     * @return float
     */
    public function getAmountCharged(): float;

    /**
     * Add an amount that has been actually paid by a payer
     * Note: Can be used to calculate the actual ally fee
     *
     * @param \App\Billing\Payment $payment
     * @param float $amount
     * @param float $allyFee The value of $amount that represents the Ally Fee
     */
    public function addAmountCharged(Payment $payment, float $amount, float $allyFee): void;

    /**
     * Get the amount that has been deposited
     *
     * @return float
     */
    public function getAmountDeposited(): float;

    /**
     * Add an amount that has been deposited
     *
     * @param \App\Billing\Deposit $deposit
     * @param float $amount
     */
    public function addAmountDeposited(Deposit $deposit, float $amount): void;

    /**
     * A query scope for filtering invoicables by related caregiver IDs
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $caregiverIds
     * @return void
     */
    public function scopeForCaregivers(Builder $builder, array $caregiverIds);

}