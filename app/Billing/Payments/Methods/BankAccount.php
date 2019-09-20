<?php

namespace App\Billing\Payments\Methods;

use App\AuditableModel;
use App\Billing\Contracts\ChargeableInterface;
use App\Billing\Contracts\DepositableInterface;
use App\Billing\GatewayTransaction;
use App\Billing\Payments\PaymentMethodType;
use App\Business;
use App\Billing\Gateway\ACHDepositInterface;
use App\Billing\Gateway\ACHPaymentInterface;
use App\Traits\ChargedTransactionsTrait;
use App\Traits\HasAllyFeeTrait;
use App\User;
use Crypt;

/**
 * App\Billing\Payments\Methods\BankAccount
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business|null $business
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\GatewayTransaction[] $chargedTransactions
 * @property-read object $charge_metrics
 * @property-read mixed $last_four
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @mixin \Eloquent
 */
class BankAccount extends AuditableModel implements ChargeableInterface, DepositableInterface
{
    use ChargedTransactionsTrait;
    use HasAllyFeeTrait;

    protected $table = 'bank_accounts';
    protected $guarded = ['id'];
    protected $hidden = ['account_number', 'routing_number'];
    protected $appends = ['last_four'];
    protected static $accountTypes = [
        'checking',
        'savings'
    ];


    public static function getAccountTypes()
    {
        return self::$accountTypes;
    }

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
        $this->setRoutingNumber($value);
    }

    public function getRoutingNumberAttribute()
    {
        return $this->getRoutingNumber();
    }

    public function setAccountNumberAttribute($value)
    {
        $this->setAccountNumber($value);
    }

    public function getAccountNumberAttribute()
    {
        return $this->getAccountNumber();
    }

    public function getLastFourRoutingNumberAttribute(){
        return substr($this->routing_number, -4);
    }

    ///////////////////////////////////////////
    /// Instance Methods
    ///////////////////////////////////////////

    function getRoutingNumber(): ?string
    {
        return empty($this->attributes['routing_number']) ? null : Crypt::decrypt($this->attributes['routing_number']);
    }

    function setRoutingNumber(?string $number)
    {
        $this->attributes['routing_number'] = $number ? Crypt::encrypt($number) : null;
    }

    function getAccountNumber(): ?string
    {
        return empty($this->attributes['account_number']) ? null : Crypt::decrypt($this->attributes['account_number']);
    }

    function setAccountNumber(?string $number)
    {
        $this->attributes['account_number'] = $number ? Crypt::encrypt($number) : null;
    }

    function getBillingName(): string
    {
        return $this->name_on_account;
    }

    function getAccountType(): string
    {
        return $this->account_type ?? "checking";
    }

    function getAccountHolderType(): string
    {
        return $this->account_type ?? "personal";
    }

    function getBillingAddress(): ?\App\Address
    {
        if ($this->user && $address = $this->user->addresses->where('type', 'billing')->first()) {
            return $address;
        } elseif ($this->user && $address = $this->user->addresses->where('type', 'evv')->first()) {
            return $address;
        } elseif ($this->user && $address = $this->user->addresses->where('type', 'primary')->first()) {
            return $address;
        } elseif ($this->business) {
            return $this->business->getAddress();
        }

        return null;
    }

    function getBillingPhone(): ?\App\PhoneNumber
    {
        if ($this->user && $phone = $this->user->phoneNumbers->where('type', 'billing')->first()) {
            return $phone;
        } elseif ($this->user && $phone = $this->user->phoneNumbers->where('type', 'primary')->first()) {
            return $phone;
        } elseif ($this->business) {
            return $this->business->getPhoneNumber();
        }

        return null;
    }

    function getPaymentType(): PaymentMethodType
    {
        return PaymentMethodType::ACH();
    }

    /**
     * @param float $amount
     * @param string $currency
     * @return \App\Billing\GatewayTransaction|false
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
     * @param \App\Billing\GatewayTransaction $transaction
     * @param $amount
     * @return \App\Billing\GatewayTransaction|false
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

    /**
     * Return the owner of the payment method or account
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getOwnerModel()
    {
        return $this->user ?? $this->business;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return 'bank_accounts:' . $this->id;
    }

    /**
     * Return a display value of the payment method.  Ex.  VISA *0925
     *
     * @return string
     */
    public function getDisplayValue(): string
    {
        return 'ACH *' . $this->last_four;
    }
}
