<?php

namespace App;

use App\Traits\BelongsToOneBusiness;

class OccAccDeductible extends AuditableModel
{

    use BelongsToOneBusiness;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the related Caregiver.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function caregiver()
    {
        return $this->belongsTo('App\Caregiver');
    }

    /**
     * Get the related caregiver adjustment invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function caregiverInvoice()
    {
        return $this->hasOne('App\Billing\CaregiverInvoice');
    }

    /**
     * Get the related shifts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shifts()
    {
        return $this->hasMany(OccAccDeductibleShift::class, 'deductible_id', 'id');
    }
}
