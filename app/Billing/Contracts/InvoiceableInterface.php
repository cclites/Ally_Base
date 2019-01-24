<?php
namespace App\Billing\Contracts;

use App\Business;
use App\Caregiver;
use App\Client;
use App\Billing\Contracts\ChargeableInterface;
use Illuminate\Support\Collection;

interface InvoiceableInterface
{
    /**
     * Collect all applicable invoiceables of this type eligible for the client payment
     *
     * @param \App\Client $client
     * @return \Illuminate\Support\Collection                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           \App\Billing\Contracts\InvoiceableInterface[]
     */
    public function getItemsForPayment(Client $client): Collection;

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
     * Get the client rate of this item (payment rate).  The total charged will be this rate multiplied by the units.
     *
     * @return float
     */
    public function getClientRate(): float;

    /**
     * TODO Implement caregiver deposit invoicing
     * @return float
     */
    public function getCaregiverRate(): float;

    /**
     * Return the ally fee per unit for this invoiceable item.  If this returns null, abort invoicing this item.  Return 0.0 for no ally fee.
     *
     * @return float|null
     */
    public function getAllyRate(): ?float;

    /**
     * TODO Implement business deposit invoicing
     * Note: This is a calculated field from the other rates
     * @return float
     */
    public function getProviderRate(): float;

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
     * @param float $amount
     */
    public function addAmountInvoiced(float $amount): void;

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
     * @param float $amount
     * @param \App\Billing\Contracts\ChargeableInterface $paymentMethod
     */
    public function addAmountCharged(float $amount, ChargeableInterface $paymentMethod): void;

    /**
     * Get the amount that has been deposited
     *
     * @return float
     */
    public function getAmountDeposited(): float;

    /**
     * Add an amount that has been deposited
     * Note: Can be used to calculate the actual ally fee
     *
     * @param float $amount
     * @param \App\Billing\Contracts\DepositableInterface $account
     */
    public function addAmountDeposited(float $amount, DepositableInterface $account): void;

}