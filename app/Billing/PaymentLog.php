<?php
namespace App\Billing;

use App\BaseModel;

/**
 * \App\Billing\PaymentLog
 *
 * @property int $id
 * @property string $batch_id
 * @property int|null $chain_id
 * @property int|null $payment_id
 * @property string|null $payment_method_type
 * @property int|null $payment_method_id
 * @property string|null $exception
 * @property string|null $error_message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\BusinessChain|null $chain
 * @property-read \Illuminate\Database\Eloquent\Model|\App\Billing\Contracts\ChargeableInterface $method
 * @property-read \App\Billing\Payment|null $payment
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\PaymentLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\PaymentLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\PaymentLog query()
 * @mixin \Eloquent
 */
class PaymentLog extends BaseModel
{
    use PaymentLogFunctions;

    protected $table = 'payment_log';
    public $guarded = ['id'];
    protected static $batchPrefix = "P";

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    public function setPayment(Payment $payment)
    {
        $this->payment()->associate($payment);
    }
}