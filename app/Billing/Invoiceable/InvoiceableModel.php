<?php
namespace App\Billing\Invoiceable;

use App\AuditableModel;
use App\Billing\Contracts\InvoiceableInterface;
use App\Billing\ClientInvoiceItem;
use App\Billing\Deposit;
use App\Billing\Payment;
use App\Billing\Queries\InvoiceableQuery;
use App\Business;
use App\Caregiver;
use App\Contracts\BelongsToBusinessesInterface;
use Illuminate\Support\Collection;
use Packages\MetaData\HasMetaData;

abstract class InvoiceableModel extends AuditableModel implements InvoiceableInterface, BelongsToBusinessesInterface
{
    use HasMetaData;

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    public function meta()
    {
        return $this->morphMany(InvoiceableMeta::class, 'metable');
    }

    public function clientInvoiceItems()
    {
        return $this->morphMany(ClientInvoiceItem::class, 'invoiceable');
    }

    ////////////////////////////////////
    //// Collection Query Methods
    ////////////////////////////////////

    /**
     * Collect all applicable invoiceables of this type eligible for the caregiver deposit
     *
     * @param \App\Caregiver $caregiver
     * @return \Illuminate\Support\Collection|\App\Billing\Contracts\InvoiceableInterface[]
     */
    public function getItemsForCaregiverDeposit(Caregiver $caregiver): Collection
    {
        $query = new InvoiceableQuery($this);
        return $query->forCaregivers([$caregiver->id])
            ->hasClientInvoicesPaid()
            ->doesntHaveCaregiverInvoice()
            ->notBelongingToAnOldFinalizedShift()
            ->get();
    }

    /**
     * Collect all applicable invoiceables of this type eligible for the provider deposit
     *
     * @param \App\Business $business
     * @return \Illuminate\Support\Collection|\App\Billing\Contracts\InvoiceableInterface[]
     */
    public function getItemsForBusinessDeposit(Business $business): Collection
    {
        $query = new InvoiceableQuery($this);
        return $query->forBusinesses([$business->id])
            ->hasClientInvoicesPaid()
            ->doesntHaveBusinessInvoice()
            ->notBelongingToAnOldFinalizedShift()
            ->get();
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////


    /**
     * Get a unique hash for this item
     *
     * @return string
     */
    public function getItemHash(): string
    {
        return $this->getTable() . '_' . $this->getKey();
    }

    /**
     * Get the assigned payer ID (payers.id, not client_payers.id)
     *
     * @return int|null
     */
    public function getPayerId(): ?int
    {
        return $this->payer_id ?? null;
    }

    /**
     * Return the ally fee per unit for this invoiceable item.
     * If this returns null, abort deposit invoices.  Return 0.0 for no ally fee.
     *
     * @return float|null
     */
    public function getAllyRate(): ?float
    {
        if ($this->getItemUnits() == 0) {
            return 0.0;
        }

        $allyFeeCharged = $this->getMetaValue("ally_fee_charged");
        if ($allyFeeCharged !== null) {
            return divide($allyFeeCharged, $this->getItemUnits(), 4);
        }
        return null;
    }

    /**
     * Note: This is a calculated field from the other rates
     * @return float
     */
    public function getProviderRate(): float
    {
        $allyRate = $this->getAllyRate();
        if ($allyRate === null && ($this->getClientRate() == 0 && $this->getCaregiverRate() == 0 || $this->getItemUnits() == 0)) {
            return 0.0;  // Fix for when the rates are set to 0 or the hours/units are set to 0 and therefore no payment is recorded/necessary.
        }

        if ($allyRate === null) {
            throw new \InvalidArgumentException("There was a problem with the Ally Fee calculation.");
        }

        return subtract($this->getClientRate(), add($this->getCaregiverRate(), $allyRate));
    }

    /**
     * Get the amount due for payment, this should subtract the amount invoiced
     *
     * @return float
     */
    public function getAmountDue(): float
    {
        $amount = bcmul($this->getClientRate(), $this->getItemUnits(), 4);
        $amount = bcsub($amount, $this->getAmountInvoiced(), 4);

        // If amount is less than 1 cent, it has already been split
        // and fully invoiced but the calculation is off.  If we
        // return 0, it resolves an issue of a -0.01 balance after invoicing.
        if ($amount > floatval(0.00) && $amount < floatval(0.01)) {
            return floatval(0.0);
        }

        return round($amount, 2);
    }

    /**
     * Get the amount that has been invoiced to the client
     *
     * @return float
     */
    public function getAmountInvoiced(): float
    {
        return (float) $this->clientInvoiceItems()->sum('amount_due');
    }

    /**
     * Get the amount that has been charged
     *
     * @return float
     */
    public function getAmountCharged(): float
    {
        return (float) $this->getMetaValue("amount_charged");
    }

    /**
     * Add an amount that has been actually paid by a payer
     * Note: Can be used to calculate the actual ally fee
     *
     * @param \App\Billing\Payment $payment
     * @param float $amount
     * @param float $allyFee The value of $amount that represents the Ally Fee
     * @throws \Packages\MetaData\Exceptions\ModelNotSavedException
     */
    public function addAmountCharged(Payment $payment, float $amount, float $allyFee): void
    {
        $charged = $this->getMetaValue("amount_charged") ?? 0.0;
        $allyFeeCharged = $this->getMetaValue("ally_fee_charged") ?? 0.0;
        $this->setMeta("amount_charged", add($charged, $amount));
        $this->setMeta("ally_fee_charged", add($allyFeeCharged, $allyFee));
        $this->addMeta("charges", json_encode(["amount" => $amount, "ally_fee" => $allyFee, "payment_id" => $payment->id]));
    }

    /**
     * Get the amount that has been deposited
     *
     * @return float
     */
    public function getAmountDeposited(): float
    {
        return (float) $this->getMetaValue("amount_deposited");
    }

    /**
     * Add an amount that has been deposited
     *
     * @param \App\Billing\Deposit $deposit
     * @param float $amount
     * @throws \Packages\MetaData\Exceptions\ModelNotSavedException
     */
    public function addAmountDeposited(Deposit $deposit, float $amount): void
    {
        $deposited = $this->getMetaValue("amount_deposited") ?? "0.00";
        $this->setMeta("amount_deposited", bcadd($deposited, $amount, 2));
        $this->addMeta("deposits", json_encode(["amount" => $amount, "deposit_id" => $deposit->id]));
    }
}