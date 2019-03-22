<?php

namespace App\Billing;

use App\AuditableModel;

class Claim extends AuditableModel
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
    public $with = ['statuses'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    ///////////////////////////////////////
    /// Claim Statuses
    ///////////////////////////////////////
    const NOT_SENT = 'NOT_SENT';
    const CREATED = 'CREATED';
    const TRANSMITTED = 'TRANSMITTED';
    const RETRANSMITTED = 'RETRANSMITTED';
    const ACCEPTED = 'ACCEPTED';
    const REJECTED = 'REJECTED';

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the ClientInvoice relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function invoice()
    {
        return $this->belongsTo(ClientInvoice::class);
    }

    /**
     * Get the status relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function statuses()
    {
        return $this->hasMany(ClaimStatus::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    /**
     * Get the current balance of the Claim.
     *
     * @return float
     */
    public function getBalanceAttribute()
    {
        // TODO: calculate based on payments
        return $this->amount;
    }

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Set the status of the claim, and add to it's status history.
     *
     * @param string $status
     */
    public function updateStatus(string $status) : void
    {
        $this->update(['status' => $status]);
        $this->statuses()->create(['status' => $status]);
    }
}
