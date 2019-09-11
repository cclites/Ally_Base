<?php

namespace App\Claims;

use App\AuditableModel;
use App\Claims\Exceptions\ClaimBalanceException;
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

    /**
     * Get the ClaimRemitApplications relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function remitApplications()
    {
        return $this->hasMany(ClaimRemitApplication::class);
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

    /**
     * Calculate the amount due for this ClaimInvoiceItem
     * from all the remit amounts applied to it.
     *
     * @return void
     * @throws ClaimBalanceException
     */
    public function updateBalance() : void
    {
        $totalApplied = $this->remitApplications->reduce(function ($carry, $application) {
            return add($carry, floatval($application->amount_applied));
        }, floatval(0));

        $amountDue = subtract(floatval($this->amount), $totalApplied);

        if ($amountDue < floatval(0)) {
            throw new ClaimBalanceException('Claim invoice items cannot have a negative balance.');
        } else if ($amountDue > floatval($this->amount)) {
            throw new ClaimBalanceException('Claim invoice items cannot have a balance greater than their total amount.');
        }

        $this->update(['amount_due' => $amountDue]);
    }
}