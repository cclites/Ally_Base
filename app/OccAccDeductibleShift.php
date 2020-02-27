<?php

namespace App;

class OccAccDeductibleShift extends AuditableModel
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the owning OccAccDeductible.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function deductible()
    {
        return $this->belongsTo(OccAccDeductible::class, 'deductible_id', 'id');
    }
}
