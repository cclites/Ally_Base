<?php
namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;
use App\Events\TimesheetCreated;
use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * App\Timesheet
 *
 * @property int $id
 * @property int $business_id
 * @property int $caregiver_id
 * @property int $client_id
 * @property int $creator_id
 * @property \Carbon\Carbon|null $approved_at
 * @property \Carbon\Carbon|null $denied_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business $business
 * @property-read \App\Caregiver $caregiver
 * @property-read \App\Client $client
 * @property-read \App\User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\TimesheetEntry[] $entries
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SystemException[] $exceptions
 * @property-read int $exception_count
 * @property-read void $is_approved
 * @property-read void $is_denied
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Timesheet forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Timesheet forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Timesheet whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Timesheet whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Timesheet whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Timesheet whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Timesheet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Timesheet whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Timesheet whereDeniedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Timesheet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Timesheet whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Timesheet extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    protected $guarded = ['id'];

    protected $dates = ['approved_at', 'denied_at'];

    protected $with = ['entries'];

    protected $appends = ['exception_count'];
    
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
    public function getIsApprovedAttribute()
    {
        return $this->approved_at != null;
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

    /**
     * Gets the number of exceptions attached to the Timesheet.
     *
     * @return int
     */
    public function getExceptionCountAttribute()
    {
        return $this->exceptions()->count();
    }

    ///////////////////////////////////////////
    /// Other
    ///////////////////////////////////////////

    /**
     * Marks all attached exceptions as acknowledged by the current 
     * logged in user with the given note.
     *
     * @param string $note
     * @return bool
     */
    public function acknowledgeExceptions($note = '')
    {
        $any = false;

        foreach($this->exceptions as $ex) {
            $ex->acknowledge($note);
            $any = true;
        }

        return $any;
    }

    /**
     * Method to mark Timesheet as approved. Also automatically ackowledges
     * any connected exceptions.
     *
     * @return void
     */
    public function approve()
    {
        $this->update([
            'approved_at' => Carbon::now(),
        ]);

        $this->acknowledgeExceptions('Timesheet Approved');
    }

    /**
     * Method to mark Timesheet as denied.  Also automatically ackowledges
     * any connected exceptions.
     *
     * @return void
     */
    public function deny()
    {
        $this->update([
            'denied_at' => Carbon::now(),
        ]);

        $this->acknowledgeExceptions('Timesheet Denied');
    }

    /**
     * Creates a Timesheet from request data and handles the sync
     * of entry data.  Returns a fresh copy of the timesheet with
     * necessary Caregiver and Client relations or false on error.
     *
     * @param array $data
     * @param \App\User $creator
     * @param \App\Business $business
     * @return \App\Timesheet
     */
    public static function createWithEntries($data, $creator, $business)
    {
        try {
            $timesheet = Timesheet::make(Arr::except($data, 'entries'));
            $timesheet->creator_id = $creator->id;
            $timesheet->business_id = $business->id;
            $timesheet->save();
            
            foreach($data['entries'] as $item) {
                $timesheet->createSingleEntry($item);
            }

            return $timesheet->fresh()->load('caregiver', 'client');
        }
        catch (\Exception $ex) {
            return false; 
        }
    }

    /**
     * Updates the current Timesheet with given request data and handles the 
     * sync of entry data.  Returns either a fresh copy of the timesheet 
     * with necessary Caregiver and Client relations or false on error.
     *
     * @param array $data
     * @return mixed
     */
    public function updateWithEntries($data)
    {
        try {
            $this->update(Arr::except($data, 'entries'));

            $this->entries()->delete();
            foreach($data['entries'] as $item) {
                $this->createSingleEntry($item);
            }

            return $this->fresh()->load('caregiver', 'client');
        }
        catch (\Exception $ex) {
            return false; 
        }
    }

    public function createSingleEntry(array $data)
    {
        if ($entry = $this->entries()->create([
            'checked_in_time' => $data['checked_in_time'],
            'checked_out_time' => $data['checked_out_time'],
            'mileage' => (float) $data['mileage'],
            'other_expenses' =>  (float) $data['other_expenses'],
            'caregiver_comments' => $data['caregiver_comments'],
            'caregiver_rate' => (float) $data['caregiver_rate'],
            'provider_fee' => (float) $data['provider_fee'],
        ])) {
            $entry->activities()->sync($data['activities']);
        }

        return $entry;
    }

    /**
     * Creates a Shift object for each Timesheet Entry.
     *
     * @return bool
     */
    public function createShiftsFromEntries()
    {
        foreach($this->entries as $entry) {
            $data['checked_in_time'] = $entry['checked_in_time'];
            $data['checked_out_time'] = $entry['checked_out_time'];
            $data['mileage'] = $entry['mileage'];
            $data['other_expenses'] = $entry['other_expenses'];
            $data['caregiver_comments'] = $entry['caregiver_comments'];
            $data['caregiver_rate'] = $entry['caregiver_rate'];
            $data['provider_fee'] = $entry['provider_fee'];

            $data['timesheet_id'] = $this->id;
            $data['caregiver_id'] = $this->caregiver_id;
            $data['client_id'] = $this->client_id;
            $data['business_id'] = $this->business_id;
            $data['checked_in_method'] = Shift::METHOD_TIMESHEET;
            $data['checked_out_method'] = Shift::METHOD_TIMESHEET;
            $data['hours_type'] = 'default';
            $data['status'] = Shift::WAITING_FOR_AUTHORIZATION;
            $data['verified'] = false;

            if ($shift = Shift::create($data)) {
                $shift->activities()->sync($entry->activities);
            } else {
                return false;
            }
        }

        return true;
    }
    
}
