<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CaregiverScheduleRequest extends Pivot
{
    protected $table = 'caregiver_schedule_requests';
    protected $orderedColumn = 'created_at';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [ 'id' ];

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

    const REQUEST_APPROVED  = 'approved';
    const REQUEST_DENIED    = 'denied';
    const REQUEST_PENDING   = 'pending';
    const REQUEST_CANCELLED = 'cancelled';

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    public function caregiver()
    {
        return $this->belongsTo( Caregiver::class );
    }

    public function business()
    {
        return $this->belongsTo( Business::class );
    }

    public function schedule()
    {
        return $this->belongsTo( Schedule::class );
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

    /**
     * fetchable path for the resource, dynamic to the role of the current user
     */
    public function path()
    {
        $url = '/schedule/requests/' . $this->id;

        if( is_caregiver() ) return '/caregiver' . $url;

        if( is_office_user() ) return '/business' . $url;

        return ''; // maybe this can be something else..
    }
}
