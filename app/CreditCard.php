<?php


namespace App;

use App\Contracts\ChargeableInterface;
use App\Gateway\CreditCardPaymentInterface;
use Carbon\Carbon;
use Crypt;
use Illuminate\Database\Eloquent\Model;

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
 * @property-read \Carbon $expiration_date
 * @property-read mixed $last_four
 * @property-read \App\User $user
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
class CreditCard extends Model implements ChargeableInterface
{
    protected $table = 'credit_cards';
    protected $guarded = ['id'];
    protected $hidden = ['number'];
    protected $appends = ['last_four', 'expiration_date'];

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
}
