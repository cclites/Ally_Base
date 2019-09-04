<?php
namespace App;

use App\Billing\Service;
use App\Claims\ClaimableInterface;

class ClaimableService extends AuditableModel implements ClaimableInterface
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
    public $with = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'scheduled_start_time',
        'scheduled_end_time',
        'visit_start_time',
        'visit_end_time',
        'evv_start_time',
        'evv_end_time',
    ];

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

    const EVV_METHOD_TELEPHONY = 'telephony';
    const EVV_METHOD_GEOLOCATION = 'geolocation';

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the related Shift.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get the related Caregiver.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    /**
     * Get the related Service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // **********************************************************
    // ACCESSORS
    // **********************************************************

    /**
     * Encrypt Caregiver SSN on entry.
     *
     * @param $value
     */
    public function setCaregiverSsnAttribute($value)
    {
        $this->attributes['caregiver_ssn'] = $value ? \Crypt::encrypt($value) : null;
    }

    /**
     * Decrypt Caregiver SSN on retrieval.
     *
     * @return null|string
     */
    public function getCaregiverSsnAttribute()
    {
        return empty($this->attributes['caregiver_ssn']) ? null : \Crypt::decrypt($this->attributes['caregiver_ssn']);
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

    // **********************************************************
    // ClaimableInterface
    // **********************************************************

    /**
     * Get the name of the Claimable Item.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->service_name . ' ' . $this->service_code;
    }
}