<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    protected $guarded = ['id'];

    protected $dates = ['confirmed_at', 'denied_at'];

    protected $with = ['entries'];
    
    //////////////////////////////////////
    /// Relationship Methods
    //////////////////////////////////////

    /**
     * A Timesheet has many Entries.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function entries()
    {   
        return $this->hasMany(TimesheetEntry::class);
    }

    /**
     * A Timesheet belongs to a Client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class)
                    ->withTrashed();
    }

    /**
     * A Timesheet belongs to a Caregiver.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class)
                    ->withTrashed();
    }

    /**
     * A Timesheet belongs to a Business.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    /**
     * Checks if Timesheet has been confirmed.
     *
     * @return void
     */
    public function getIsConfirmedAttribute()
    {
        return $this->confirmed_at != null;
    }

    /**
     * Checks if Timesheet has been denied.
     *
     * @return void
     */
    public function getIsDeniedAttribute()
    {
        return $this->denied_at != null;
    }
}
