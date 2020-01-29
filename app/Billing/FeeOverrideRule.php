<?php

namespace App\Billing;

use App\Billing\Payments\PaymentMethodType;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Billing\FeeOverrideRule
 *
 * @property int $id
 * @property int|null $business_id
 * @property int|null $client_id
 * @property string $payment_method_type
 * @property float $rate
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\FeeOverrideRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\FeeOverrideRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\FeeOverrideRule query()
 * @mixin \Eloquent
 */
class FeeOverrideRule extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    // **********************************************************
    // ACCESSORS
    // **********************************************************

    /**
     * Get the rate of the override.
     *
     * @return float
     */
    public function getRate() : float
    {
        return (float) $this->rate;
    }

    // **********************************************************
    // STATIC METHODS
    // **********************************************************

    /**
     * Find a fee override for the given business/payment method.
     *
     * @param int|null $businessId
     * @param PaymentMethodType $type
     * @return static|null
     */
    public static function lookup(?int $businessId, PaymentMethodType $type) : ?self
    {
        return self::where('payment_method_type', $type->getValue())
            ->where('business_id', $businessId)
            ->first();
    }
}
