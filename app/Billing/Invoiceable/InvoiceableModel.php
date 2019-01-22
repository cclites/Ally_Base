<?php
namespace App\Billing\Invoiceable;

use App\AuditableModel;
use App\Billing\Contracts\ChargeableInterface;
use App\Billing\Contracts\DepositableInterface;
use App\Billing\Contracts\InvoiceableInterface;
use App\Billing\InvoiceItem;
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

    public function invoiceItems()
    {
        return $this->morphMany(InvoiceItem::class, 'invoiceable');
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

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
     * Get the amount that has been invoiced
     *
     * @return float
     */
    public function getAmountInvoiced(): float
    {
        return (float) $this->invoiceItems()->sum('amount_due');
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
     * @param float $amount
     * @param \App\Billing\Contracts\ChargeableInterface $paymentMethod
     */
    public function addAmountCharged(float $amount, ChargeableInterface $paymentMethod): void
    {
        $charged = $this->getMetaValue("amount_charged") ?? "0.00";
        $this->setMeta("amount_charged", bcadd($charged, $amount, 2));
        $this->addMeta("charges", json_encode(["amount" => $amount, "method_type" => get_class($paymentMethod)] + $paymentMethod->toArray()));
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
     * Note: Can be used to calculate the actual ally fee
     *
     * @param float $amount
     * @param \App\Billing\Contracts\DepositableInterface $account
     */
    public function addAmountDeposited(float $amount, DepositableInterface $account): void
    {
        $deposited = $this->getMetaValue("amount_deposited") ?? "0.00";
        $this->setMeta("amount_deposited", bcadd($deposited, $amount, 2));
        $this->addMeta("deposits", json_encode(["amount" => $amount, "account_type" => get_class($account)] + $account->toArray()));
    }
}