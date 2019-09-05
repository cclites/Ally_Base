<?php

namespace App\Claims;

use App\AuditableModel;
use Carbon\Carbon;

class ClaimInvoiceItem extends AuditableModel
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
    public $with = ['claimable'];

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
     * Get the parent ClaimInvoice relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function claim()
    {
        return $this->belongsTo(ClaimInvoice::class, 'claim_invoice_id');
    }

    /**
     * Get the claimable object.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function claimable()
    {
        return $this->morphTo('claimable', 'claimable_type', 'claimable_id');
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

    public function getShift()
    {
        return $this->claimable->shift;
    }

    public function getShiftName()
    {
        if ($this->claimable instanceof ClaimableExpense) return $this->claimable->name;

        return $this->getService()->name;
    }

    public function getShiftTitle()
    {
        $shift = $this->getShift();

        return Carbon::parse($shift->checked_in_time)->format('M d') . ' ' . Carbon::parse($shift->checked_in_time)->format('h:iA') . ' - ' . Carbon::parse($shift->checked_out_time)->format('h:iA') . ': ' . $shift->caregiver->name;
    }

    public function getCaregiver()
    {
        if ($this->claimable instanceof ClaimableService) return $this->claimable->caregiver;
        return null;
    }

    public function getService()
    {
        if ($this->claimable instanceof ClaimableService) return $this->claimable->service;
        return null;
    }
}