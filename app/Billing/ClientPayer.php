<?php
namespace App\Billing;

use App\AuditableModel;
use App\Client;
use App\Contracts\ChargeableInterface;

/**
 * App\Billing\ClientPayer
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Client $client
 * @property-read \App\Billing\Payer $payer
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @mixin \Eloquent
 */
class ClientPayer extends AuditableModel
{
    protected $orderedColumn = 'priority';
    protected $guarded = ['id'];
    protected $with = ['payer'];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function client()
    {
        return $this->belongsTo(Client::class);
    }

    function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    /**
     * Get the payment method for this payer
     *
     * @return \App\Contracts\ChargeableInterface
     */
    function getPaymentMethod(): ChargeableInterface
    {
        if ($this->payer_id === null) {
            // Private pay
            return $this->client->getPaymentMethod();
        }

        // Fall back to provider pay for all other payments.
        return $this->client->business;
    }
}