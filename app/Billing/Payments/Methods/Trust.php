<?php
namespace App\Billing\Payments\Methods;

use App\AuditableModel;
use App\Billing\BillingCalculator;
use App\Billing\Contracts\ChargeableInterface;
use App\Billing\GatewayTransaction;
use App\Billing\Payments\PaymentMethodType;
use App\Client;
use App\Traits\ChargedTransactionsTrait;
use App\Traits\HasAllyFeeTrait;

class Trust extends AuditableModel implements ChargeableInterface
{
    use ChargedTransactionsTrait;
    use HasAllyFeeTrait;

    protected $fillable = ['client_id'];

    ////////////////////////////////////
    //// Factory
    ////////////////////////////////////

    public static function firstOrCreate(Client $client)
    {
        if (!$trust = Trust::where('client_id', $client->id)->first()) {
            $trust = Trust::create(['client_id' => $client->id]);
        }

        return $trust;
    }

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    ////////////////////////////////////
    //// Mutators
    ////////////////////////////////////

    public function setUserIdAttribute(int $value)
    {
        $this->attributes['client_id'] = $value;
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Return the name on the account or card
     *
     * @return string
     */
    public function getBillingName(): string
    {
        return 'Trust';
    }

    function getBillingAddress(): ?\App\Address
    {
        if ($this->getClient() && $address = $this->getClient()->addresses->where('type', 'billing')->first()) {
            return $address;
        } elseif ($this->getClient() && $address = $this->getClient()->addresses->where('type', 'evv')->first()) {
            return $address;
        }

        return null;
    }

    function getBillingPhone(): ?\App\PhoneNumber
    {
        if ($this->getClient() && $phone = $this->getClient()->phoneNumbers->where('type', 'billing')->first()) {
            return $phone;
        } elseif ($this->getClient() && $phone = $this->getClient()->phoneNumbers->where('type', 'primary')->first()) {
            return $phone;
        }

        return null;
    }

    function getPaymentType(): PaymentMethodType
    {
        return PaymentMethodType::TRUST();
    }


    /**
     * @return string
     */
    public function getHash(): string
    {
        return 'trusts:' . $this->getKey();
    }

    /**
     * Return a display value of the payment method.  Ex.  VISA *0925
     *
     * @return string
     */
    public function getDisplayValue(): string
    {
        return 'TRUST';
    }

    /**
     * Determine if the existing record can be updated
     * This is used for the preservation of payment method on transaction history records
     *
     * @param \App\Billing\Contracts\ChargeableInterface $newPaymentMethod
     * @return bool
     */
    public function canBeMergedWith(ChargeableInterface $newPaymentMethod)
    {
        return false;
    }

    /**
     * Merge the existing record with the new values
     *
     * @param \App\Billing\Contracts\ChargeableInterface $newPaymentMethod
     * @return bool
     */
    public function mergeWith(ChargeableInterface $newPaymentMethod)
    {
        return false;
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
        return false;
    }

    /**
     * Save a new Chargeable instance to the database
     */
    public function persistChargeable()
    {
        return $this->save();
    }

    /**
     * Return the owner of the payment method or account
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getOwnerModel()
    {
        return $this->getClient();
    }

    /**
     * Get the ally fee percentage for this entity
     *
     * @return float
     */
    public function getAllyPercentage()
    {
        return BillingCalculator::getTrustRate();
    }
}