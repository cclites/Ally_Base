<?php
namespace App\Contracts;

interface HasPaymentHold
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function paymentHold();

    /**
     * @return bool
     */
    public function isOnHold();

    /**
     * @return bool
     */
    public function addHold();

    /**
     * @return bool
     */
    public function removeHold();
}