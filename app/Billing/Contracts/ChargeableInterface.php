<?php
namespace App\Billing\Contracts;

use App\Address;
use App\Billing\Payments\Contracts\PaymentMethodStrategy;
use App\Contracts\HasAllyFeeInterface;
use App\Billing\GatewayTransaction;
use App\PhoneNumber;

interface ChargeableInterface extends HasAllyFeeInterface
{
    /**
     * @return \App\Address|null
     */
    public function getBillingAddress(): ?Address;

    /**
     * @return \App\PhoneNumber|null
     */
    public function getBillingPhone(): ?PhoneNumber;

    /**
     * @return \App\Billing\Payments\Contracts\PaymentMethodStrategy
     */
    public function getPaymentStrategy(): PaymentMethodStrategy;

    /**
     * @return string
     */
    public function getHash(): string;

    /**
     * Determine if the existing record can be updated
     * This is used for the preservation of payment method on transaction history records
     *
     * @param \App\Billing\Contracts\ChargeableInterface $newPaymentMethod
     * @return bool
     */
    public function canBeMergedWith(ChargeableInterface $newPaymentMethod);

    /**
     * Merge the existing record with the new values
     *
     * @param \App\Billing\Contracts\ChargeableInterface $newPaymentMethod
     * @return bool
     */
    public function mergeWith(ChargeableInterface $newPaymentMethod);

    /**
     * Refund a previously charged transaction
     *
     * @param \App\Billing\GatewayTransaction $transaction
     * @param $amount
     * @return \App\Billing\GatewayTransaction|false
     */
    public function refund(GatewayTransaction $transaction, $amount);

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public function getAttributes();

    /**
     * Save a new Chargeable instance to the database
     */
    public function persistChargeable();

    /**
     * Relationship to Gateway Transactions (method)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function chargedTransactions();

    /**
     * Return the owner of the payment method or account
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getOwnerModel();

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray();
}