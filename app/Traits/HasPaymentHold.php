<?php
namespace App\Traits;

use App\PaymentHold;
use Carbon\Carbon;

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


    public function addHold(string $notes = null, Carbon $check_back_on = null): bool
    {
        if (!$this->isOnHold()) {
            $hold = new PaymentHold([
                'notes' => $notes,
                'check_back_on' => $check_back_on ? $check_back_on->toDateString() : null,
            ]);
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