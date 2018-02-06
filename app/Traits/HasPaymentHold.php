<?php
namespace App\Traits;

use App\PaymentHold;

trait HasPaymentHold
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function paymentHold()
    {
        return $this->hasOne(PaymentHold::class, 'user_id');
    }

    /**
     * @return bool
     */
    public function isOnHold()
    {
        return $this->paymentHold()->exists();
    }

    /**
     * @param bool $hold
     * @return bool
     */
    public function addHold()
    {
        if (!$this->isOnHold()) {
            $hold = new PaymentHold();
            return (bool) $this->paymentHold()->save($hold);
        }
        return true;
    }

    /**
     * @return bool
     */
    public function removeHold()
    {
        if ($this->isOnHold()) {
            return (bool) $this->paymentHold()->delete();
        }
        return true;
    }
}