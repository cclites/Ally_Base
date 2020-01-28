<?php

namespace App;

use App\Billing\ClientRate;
use App\Scheduling\OpenShiftRequestStatus;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\CaregiverScheduleRequest
 *
 * @property int $id
 * @property int $business_id
 * @property int $client_id
 * @property int $caregiver_id
 * @property int $schedule_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Business $business
 * @property-read \App\Caregiver $caregiver
 * @property-read \App\Client $client
 * @property-read \App\Schedule $schedule
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverScheduleRequest forOpenSchedules()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverScheduleRequest forSchedulesInTheNextMonth($timezone)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverScheduleRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverScheduleRequest newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\CaregiverScheduleRequest onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverScheduleRequest query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverScheduleRequest whereActive()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverScheduleRequest whereActiveOrUninterested()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverScheduleRequest wherePending()
 * @method static \Illuminate\Database\Query\Builder|\App\CaregiverScheduleRequest withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\CaregiverScheduleRequest withoutTrashed()
 * @mixin \Eloquent
 */
class CaregiverScheduleRequest extends Pivot
{
    use SoftDeletes;

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

    // misc error code constants
    const ERROR_SCHEDULE_TAKEN_RACE_CONDITION = 501;
    const ERROR_REQUEST_DENIED_AND_CAREGIVER_TRIED_AGAIN = 502;

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
     * Could probably be named better..
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeWhereActive( $query )
    {
        $query->whereIn( 'status', [ OpenShiftRequestStatus::REQUEST_PENDING() ] );
    }

    /**
     * Get only requests that are 'Uninterested'
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeWhereActiveOrUninterested( $query )
    {
        $query->whereIn( 'status', [ OpenShiftRequestStatus::REQUEST_PENDING(), OpenShiftRequestStatus::REQUEST_UNINTERESTED() ] );
    }

    /**
     * Get only requests that are 'pending'
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeWherePending( $query )
    {
        $query->where( 'status', OpenShiftRequestStatus::REQUEST_PENDING() );
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
    public function caregiverClientRelationshipExists()
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
}
