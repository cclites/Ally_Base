<?php

namespace App\Claims;

use App\AuditableModel;
use App\Billing\ClaimPayment;
use App\Billing\ClientPayer;
use App\Billing\Contracts\InvoiceInterface;
use App\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ClaimInvoice extends AuditableModel implements InvoiceInterface
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    public $with = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        //
        parent::boot();
    }

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the ClaimInvoiceItems relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function items()
    {
        return $this->hasMany(ClaimInvoiceItem::class, 'claim_invoice_id', 'id');
    }

    function client()
    {
        return $this->belongsTo( Client::class );
    }

    function clientPayer()
    {
        return $this->belongsTo( ClientPayer::class );
    }

    /**
     * ERIK TODO => figure out if this is real and/or necessary, consult docs for next ticket about payment system
    */
    public function payments()
    {
        return $this->hasMany( ClaimPayment::class );
    }

    // **********************************************************
    // ACCESSORS
    // **********************************************************

    function getClientPayer(): ?ClientPayer
    {
        return $this->clientPayer;
    }

    function getAmount(): float
    {
        return ( float ) $this->amount;
    }

    function getAmountDue(): float
    {
        return ( float ) $this->amount_due;
    }

    function getAmountPaid(): float
    {
        return ( float ) $this->getAmount() - $this->getAmountDue();
    }

    function getName(): string
    {
        return $this->name;
    }

    function getDate(): string
    {
        return $this->created_at->format( 'm/d/Y' );
    }

    /**
     * @return \Illuminate\Support\Collection|\App\Billing\ClaimInvoiceItem[]
     */
    function getItems(): Collection
    {
        return $this->items;
    }

    function getItemGroups(): Collection
    {
        // ERIK TODO => figure out how this specifically applies to this model..
        return $this->getItems()->sortBy( 'created_at' )->groupBy( 'claimable_type' );
        // return $this->getItems()->sortBy( 'date' )->groupBy( 'group' ); <= the original
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    // **********************************************************
    // STATIC METHODS
    // **********************************************************

    /**
     * Get the next invoice name for a client
     *
     * @param int $businessId
     * @return string
     */
    public static function getNextName(int $businessId)
    {
        $lastName = self::where('business_id', $businessId)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->value('name');

        $minId = 1000;
        if (! $lastName) {
            $nextId = $minId;
        } else {
            $nextId = (int) substr($lastName, strpos($lastName, '-') + 1) + 1;
        }

        if ($nextId < $minId) {
            $nextId = $minId;
        }

        return "${businessId}-${nextId}";
    }
}
