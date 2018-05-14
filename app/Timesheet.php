<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Events\TimesheetCreated;

class Timesheet extends Model
{
    protected $guarded = ['id'];

    protected $dates = ['confirmed_at', 'denied_at'];

    protected $with = ['entries'];
    
    protected $dispatchesEvents = [
        'created' => TimesheetCreated::class,
    ];

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
     * A Timesheet belongs to a Creator.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class);
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

    /**
     * A Timesheet can have many SystemExceptions.
     *
     * @return void
     */
    public function exceptions()
    {
        return $this->morphMany(SystemException::class, 'reference');
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
