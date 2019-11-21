<?php

namespace App\Billing\Payments\Methods;

use App\AuditableModel;
use App\Billing\Contracts\ChargeableInterface;
use App\Billing\GatewayTransaction;
use App\Billing\Gateway\CreditCardPaymentInterface;
use App\Billing\Payments\PaymentMethodType;
use App\Traits\ChargedTransactionsTrait;
use App\Traits\HasAllyFeeTrait;
use App\Traits\ScrubsForSeeding;
use App\User;
use Carbon\Carbon;
use Crypt;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Billing\Payments\Methods\CreditCard
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\GatewayTransaction[] $chargedTransactions
 * @property-read object $charge_metrics
 * @property-read \Carbon $expiration_date
 * @property-read mixed $last_four
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @mixin \Eloquent
 */
class CreditCard extends AuditableModel implements ChargeableInterface
{
    use ChargedTransactionsTrait;
    use HasAllyFeeTrait;
    use ScrubsForSeeding;

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
        $validation = \Inacho\CreditCard::validCreditCard($number);
        return empty($validation['type']) ? null : (string) $validation['type'];
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

    public function getBillingName(): string
    {
        return $this->name_on_card;
    }

    public function getBillingAddress(): ?\App\Address
    {
        if ($this->user && $address = $this->user->addresses->where('type', 'billing')->first()) {
            return $address;
        } elseif ($this->user && $address = $this->user->addresses->where('type', 'evv')->first()) {
            return $address;
        }
        return null;
    }

    public function getBillingPhone(): ?\App\PhoneNumber
    {
        if ($this->user && $phone = $this->user->phoneNumbers->where('type', 'billing')->first()) {
            return $phone;
        } elseif ($this->user && $phone = $this->user->phoneNumbers->where('type', 'primary')->first()) {
            return $phone;
        }
        return null;
    }

    public function getPaymentType(): PaymentMethodType
    {
        return $this->type === 'amex' ? PaymentMethodType::AMEX() : PaymentMethodType::CC();
    }

    /**
     * @param float $amount
     * @param string $currency
     * @return \App\Billing\GatewayTransaction|false
     */
    public function charge($amount, $currency = 'USD')
    {
        $gateway = app()->make(CreditCardPaymentInterface::class);

        if ($address = $this->getBillingAddress()) {
            $gateway->setBillingAddress($address);
        }

        if ($phone = $this->getBillingPhone()) {
            $gateway->setBillingPhone($phone);
        }

        return $gateway->chargeCard($this, $amount, $currency);
    }

    /**
     * Refund a previously charged transaction
     *
     * @param \App\Billing\GatewayTransaction $transaction
     * @param $amount
     * @return \App\Billing\GatewayTransaction|false
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
            if (! $newPaymentMethod->number) {
                return true;
            }
            // If number is present, check that there are no differences
            if (! array_diff($this->only('number'), $newPaymentMethod->only('number'))) {
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
        foreach (array_keys($newPaymentMethod->getAttributes()) as $key) {
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
            $fee = config('ally.amex_card_fee');
        }

        return (float) $fee;
    }

    /**
     * Return the owner of the payment method or account
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getOwnerModel()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return 'credit_cards:' . $this->id;
    }

    /**
     * Return a display value of the payment method.  Ex.  VISA *0925
     *
     * @return string
     */
    public function getDisplayValue(): string
    {
        return strtoupper($this->type) . ' *' . $this->last_four;
    }

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        return [
            'name_on_card' => $faker->name,
            'number' => $fast ? \Crypt::encrypt($faker->creditCardNumber) : $faker->creditCardNumber,
            'nickname' => $faker->word,
        ];
    }
}
