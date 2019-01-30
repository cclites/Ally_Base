<?php
namespace App;

use App\Contracts\ChargeableInterface;
use App\Gateway\CreditCardPaymentInterface;
use App\Traits\ChargedTransactionsTrait;
use App\Traits\HasAllyFeeTrait;
use Carbon\Carbon;
use Crypt;

/**
 * App\CreditCard
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $nickname
 * @property string|null $name_on_card
 * @property string|null $type
 * @property mixed|null $number
 * @property int|null $expiration_month
 * @property int|null $expiration_year
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\GatewayTransaction[] $chargedTransactions
 * @property-read object $charge_metrics
 * @property-read \Carbon $expiration_date
 * @property-read mixed $last_four
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereExpirationMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereExpirationYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereNameOnCard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereUserId($value)
 * @mixin \Eloquent
 */
class CreditCard extends AuditableModel implements ChargeableInterface
{
    use ChargedTransactionsTrait;
    use HasAllyFeeTrait;

    protected $table = 'credit_cards';
    protected $guarded = ['id'];
    protected $hidden = ['number'];
    protected $appends = ['last_four', 'expiration_date'];

    /**
     * Determine the credit card type based on the provider number
     *
     * @param string $number
     * @return string|null
     */
    public static function getType($number)
    {
        return \Inacho\CreditCard::validCreditCard($number)['type'] ?: null;
    }

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    public function setNumberAttribute($value)
    {
        $this->attributes['number'] = Crypt::encrypt($value);
        $this->attributes['type'] = self::getType($value);
    }

    public function getNumberAttribute()
    {
        return empty($this->attributes['number']) ? null : Crypt::decrypt($this->attributes['number']);
    }

    public function getLastFourAttribute()
    {
        return substr($this->number, -4);
    }

    /**
     * @return Carbon
     */
    public function getExpirationDateAttribute()
    {
        $date = Carbon::parse("$this->expiration_year-$this->expiration_month-01");
        $date->endOfMonth();
        return $date;
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    /**
     * @param float $amount
     * @param string $currency
     * @return \App\GatewayTransaction|false
     */
    public function charge($amount, $currency = 'USD')
    {
        $gateway = app()->make(CreditCardPaymentInterface::class);

        if ($this->user && $address = $this->user->addresses->where('type', 'billing')->first()) {
            $gateway->setBillingAddress($address);
        } elseif ($this->user && $address = $this->user->addresses->where('type', 'primary')->first()) {
            $gateway->setBillingAddress($address);
        }

        if ($this->user && $phone = $this->user->phoneNumbers->where('type', 'billing')->first()) {
            $gateway->setBillingPhone($phone);
        } elseif ($this->user && $phone = $this->user->phoneNumbers->where('type', 'primary')->first()) {
            $gateway->setBillingPhone($phone);
        }

        return $gateway->chargeCard($this, $amount, $currency);
    }

    /**
     * Refund a previously charged transaction
     *
     * @param \App\GatewayTransaction $transaction
     * @param $amount
     * @return \App\GatewayTransaction|false
     */
    public function refund(GatewayTransaction $transaction, $amount)
    {
        $gateway = app()->make(CreditCardPaymentInterface::class);
        return $gateway->refund($transaction, $amount);
    }

    /**
     * Determine if a new database record needs to be created
     * This is used for the preservation of payment method on transaction history records
     *
     * @return bool
     */
    public function canBeMergedWith(ChargeableInterface $newPaymentMethod)
    {
        if ($newPaymentMethod instanceof self) {
            if (!$newPaymentMethod->number) {
                return true;
            }
            // If number is present, check that there are no differences
            if (!array_diff($this->only('number'), $newPaymentMethod->only('number'))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Merge the existing record with the new values
     *
     * @return bool
     */
    public function mergeWith(ChargeableInterface $newPaymentMethod)
    {
        // This loop provides support for mutations (encrypted values)
        foreach(array_keys($newPaymentMethod->getAttributes()) as $key) {
            $this->$key = $newPaymentMethod->$key;
        }
        return $this->save();
    }

    /**
     * Save a new Chargeable instance to the database
     */
    public function persistChargeable()
    {
        return $this->save();
    }

    /**
     * Get the ally fee percentage for this entity
     *
     * @return float
     */
    public function getAllyPercentage()
    {
        $fee = config('ally.credit_card_fee');
        if (strtolower($this->type) === 'amex') {
            return (float) bcadd($fee, '0.01', 4);
        }
        return (float) $fee;
    }
}
