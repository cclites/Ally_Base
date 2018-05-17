<?php

namespace App;

use App\Contracts\ChargeableInterface;
use App\Gateway\ACHDepositInterface;
use App\Gateway\ACHPaymentInterface;
use App\Traits\ChargedTransactionsTrait;
use App\Traits\HasAllyFeeTrait;
use Crypt;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\BankAccount
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $business_id
 * @property string|null $nickname
 * @property mixed $routing_number
 * @property mixed $account_number
 * @property string $account_type
 * @property string $account_holder_type
 * @property string $name_on_account
 * @property int $verified
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read mixed $last_four
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereAccountHolderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereAccountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereNameOnAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereRoutingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereVerified($value)
 * @mixin \Eloquent
 * @property-read \App\Business|null $business
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\GatewayTransaction[] $transactions
 */
class BankAccount extends Model implements ChargeableInterface, Auditable
{
    use ChargedTransactionsTrait;
    use HasAllyFeeTrait;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'bank_accounts';
    protected $guarded = ['id'];
    protected $hidden = ['account_number', 'routing_number'];
    protected $appends = ['last_four'];
    protected static $accountTypes = [
        'checking',
        'savings'
    ];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    public function getLastFourAttribute()
    {
        return substr($this->account_number, -4);
    }

    public function setRoutingNumberAttribute($value)
    {
        $this->attributes['routing_number'] = Crypt::encrypt($value);
    }

    public function getRoutingNumberAttribute()
    {
        return empty($this->attributes['routing_number']) ? null : Crypt::decrypt($this->attributes['routing_number']);
    }

    public function setAccountNumberAttribute($value)
    {
        $this->attributes['account_number'] = Crypt::encrypt($value);
    }

    public function getAccountNumberAttribute()
    {
        return empty($this->attributes['account_number']) ? null : Crypt::decrypt($this->attributes['account_number']);
    }

    ///////////////////////////////////////////
    /// Instance Methods
    ///////////////////////////////////////////

    public static function getAccountTypes()
    {
        return self::$accountTypes;
    }

    /**
     * @param float $amount
     * @param string $currency
     * @return \App\GatewayTransaction|false
     */
    public function charge($amount, $currency = 'USD')
    {
        $gateway = app()->make(ACHPaymentInterface::class);

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

        return $gateway->chargeAccount($this, $amount, $currency);
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
        $gateway = app()->make(ACHDepositInterface::class);
        return $gateway->depositFunds($this, $amount);
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
            if (!$newPaymentMethod->account_number && !$newPaymentMethod->routing_number) {
                return true;
            }
            // If routing number and account number are present, check that there are no differences
            if (!array_diff($this->only('account_number', 'routing_number'), $newPaymentMethod->only('account_number', 'routing_number'))) {
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
        return (float) config('ally.bank_account_fee');
    }
}
