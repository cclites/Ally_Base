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

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding { getScrubQuery as parentGetScrubQuery; }

    /**
     * Get the query used to identify records that will be scrubbed.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getScrubQuery() : \Illuminate\Database\Eloquent\Builder
    {
        return static::parentGetScrubQuery()->whereNotNull('error_message');
    }

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|\Illuminate\Database\Eloquent\Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        return [
            'error_message' => $faker->sentence,
        ];
    }
}