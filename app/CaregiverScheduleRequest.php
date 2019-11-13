<?php

namespace App;

use App\Billing\ClientRate;
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

    public function client()
    {
        return $this->belongsTo( Client::class );
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

    /**
     * Get only requests for schedules that are open, without a caregiver
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeForOpenSchedules( $query )
    {
        $query->whereHas( 'schedule', function( $q ){

            $q->whereOpen();
        });
    }

    /**
     * Get only requests that are 'pending'
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeWherePending( $query )
    {
        $query->where( 'status', self::REQUEST_PENDING );
    }

    /**
     * Get only requests that are 'pending'
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeForSchedulesInTheNextMonth( $query, $timezone )
    {
        $query->whereHas( 'schedule', function( $q ) use ( $timezone ){

            $q->inTheNextMonth( $timezone );
        });
    }


    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * finds a related ClientRate between the caregiver and client associated with the Schedule Request
     */
    public function caregiver_client_relationship_exists()
    {
        return ClientRate::where( 'client_id', $this->client_id )->where( 'caregiver_id', $this->caregiver_id )->exists();
    }

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

    public static function is_acceptable_status( $status )
    {
        // could probably change this to return the array itself and then call it using if( in_array() ) to extend the usefullness of this..
        return in_array( $status, [

            self::REQUEST_APPROVED,
            self::REQUEST_DENIED,
            self::REQUEST_PENDING,
            self::REQUEST_CANCELLED
        ]);
    }
}
