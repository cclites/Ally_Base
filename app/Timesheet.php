<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Events\TimesheetCreated;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use OwenIt\Auditing\Contracts\Auditable;

class Timesheet extends Model
{
    use \OwenIt\Auditing\Auditable;
    
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
     * @param App\User $creator
     * @param App\Business $business
     * @return App\Timesheet
     */
    public static function createWithEntries($data, $creator, $business)
    {
        try {
            $timesheet = Timesheet::make(Arr::except($data, 'entries'));
            $timesheet->creator_id = $creator->id;
            $timesheet->business_id = $business->id;
            $timesheet->save();
            
            foreach($data['entries'] as $item) {
                if ($entry = $timesheet->entries()->create(Arr::except($item, ['activities', 'duration', 'start_time', 'end_time', 'date']))) {
                    $entry->activities()->sync($item['activities']);
                } 
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
                if ($entry = $this->entries()->create(Arr::except($item, ['activities', 'duration', 'start_time', 'end_time', 'date']))) {
                    $entry->activities()->sync($item['activities']);
                }
            }

            return $this->fresh()->load('caregiver', 'client');
        }
        catch (\Exception $ex) {
            return false; 
        }
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
