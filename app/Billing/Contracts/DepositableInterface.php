<?php
namespace App\Billing\Contracts;

use Illuminate\Contracts\Support\Arrayable;

interface DepositableInterface extends Arrayable
{
    /**
     * @param float $amount
     * @param string $currency
     * @return \App\GatewayTransaction|false
     */
    public function depositFunds($amount, $currency = 'USD');

    /**
     * @param float $amount
     * @param string $currency
     * @return \App\GatewayTransaction|false
     */
    public function withdrawFunds($amount, $currency = 'USD');

    /**
     * Return the owner of the payment method or account
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getOwnerModel();
}