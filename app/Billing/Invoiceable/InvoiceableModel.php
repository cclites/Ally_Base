<?php
namespace App\Billing\Invoiceable;

use App\AuditableModel;
use App\Billing\Contracts\ChargeableInterface;
use App\Billing\Contracts\DepositableInterface;
use App\Billing\Contracts\InvoiceableInterface;
use App\Billing\ClientInvoiceItem;
use App\Billing\Deposit;
use App\Billing\Payment;
use Packages\MetaData\HasMetaData;

abstract class InvoiceableModel extends AuditableModel implements InvoiceableInterface
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
     * Get the amount due for payment, this should subtract the amount invoiced
     *
     * @return float
     */
    public function getAmountDue(): float
    {
        $amount = bcmul($this->getClientRate(), $this->getItemUnits(), 4);
        $amount = bcsub($amount, $this->getAmountInvoiced(), 4);
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
     */
    public function addAmountDeposited(Deposit $deposit, float $amount): void
    {
        $deposited = $this->getMetaValue("amount_deposited") ?? "0.00";
        $this->setMeta("amount_deposited", bcadd($deposited, $amount, 2));
        $this->addMeta("deposits", json_encode(["amount" => $amount, "deposit_id" => $deposit->id]));
    }
}