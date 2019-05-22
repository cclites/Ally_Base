<?php
namespace App\Contracts;

use Carbon\Carbon;

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


    public function addHold(string $notes = null, Carbon $check_back_on = null): bool;

    /**
     * @return bool
     */
    public function removeHold();
}