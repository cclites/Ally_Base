<?php
namespace App\Contracts;

use App\GatewayTransaction;

interface ChargeableInterface
{
    /**
     * @param float $amount
     * @param string $currency
     * @return \App\GatewayTransaction|false
     */
    public function charge($amount, $currency = 'USD');

    /**
     * Determine if the existing record can be updated
     * This is used for the preservation of payment method on transaction history records
     *
     * @return bool
     */
    public function canBeMergedWith(ChargeableInterface $newPaymentMethod);

    /**
     * Merge the existing record with the new values
     *
     * @return bool
     */
    public function mergeWith(ChargeableInterface $newPaymentMethod);

    /**
     * Refund a previously charged transaction
     *
     * @param \App\GatewayTransaction $transaction
     * @param $amount
     * @return \App\GatewayTransaction|false
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
}