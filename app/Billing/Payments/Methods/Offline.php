<?php
namespace App\Billing\Payments\Methods;

use App\Address;
use App\Billing\Contracts\ChargeableInterface;
use App\Billing\GatewayTransaction;
use App\Billing\Payments\Contracts\PaymentMethodStrategy;
use App\Billing\Payments\OfflinePayment;
use App\Billing\Payments\PaymentMethodType;
use App\PhoneNumber;
use App\Traits\ChargedTransactionsTrait;
use App\Traits\HasAllyFeeTrait;

/**
 * Class OfflineOnly, a Null Object Payment Method
 * @package App\Billing\Payments\Methods
 */
class Offline implements ChargeableInterface
{
    use ChargedTransactionsTrait;
    use HasAllyFeeTrait;

    /**
     * Return the name on the account or card
     *
     * @return string
     */
    public function getBillingName(): string
    {
        return "OFFLINE";
    }

    /**
     * @return \App\Address|null
     */
    public function getBillingAddress(): ?Address
    {
        return null;
    }

    /**
     * @return \App\PhoneNumber|null
     */
    public function getBillingPhone(): ?PhoneNumber
    {
        return null;
    }

    public function getPaymentType(): PaymentMethodType
    {
        return PaymentMethodType::NONE();
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return "offline";
    }

    /**
     * Return a display value of the payment method.  Ex.  VISA *0925
     *
     * @return string
     */
    public function getDisplayValue(): string
    {
        return 'OFFLINE';
    }

    /**
     * Determine if the existing record can be updated
     * This is used for the preservation of payment method on transaction history records
     *
     * @param \App\Billing\Contracts\ChargeableInterface $newPaymentMethod
     * @return bool
     */
    public function canBeMergedWith(ChargeableInterface $newPaymentMethod)
    {
        return false;
    }

    /**
     * Merge the existing record with the new values
     *
     * @param \App\Billing\Contracts\ChargeableInterface $newPaymentMethod
     * @return bool
     */
    public function mergeWith(ChargeableInterface $newPaymentMethod)
    {
        return false;
    }

    /**
     * Refund a previously charged transaction
     *
     * @param \App\Billing\GatewayTransaction $transaction
     * @param $amount
     * @return \App\Billing\GatewayTransaction|false
     */
    public function refund(GatewayTransaction $transaction, $amount)
    {
        return false;
    }

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public function getAttributes()
    {
        return [];
    }

    /**
     * Save a new Chargeable instance to the database
     */
    public function persistChargeable()
    {
        return false;
    }

    /**
     * Return the owner of the payment method or account
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getOwnerModel()
    {
        return null;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [];
    }

    /**
     * Get the ally fee percentage for this entity
     *
     * @return float
     */
    public function getAllyPercentage()
    {
        return 0;
    }
}