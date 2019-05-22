<?php
namespace App\Billing\Contracts;

use App\Billing\Payments\Contracts\DepositMethodStrategy;
use Illuminate\Contracts\Support\Arrayable;

interface DepositableInterface extends Arrayable
{

    /**
     * Return the owner of the payment method or account
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getOwnerModel();
}