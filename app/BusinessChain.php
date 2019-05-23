<?php
namespace App;

use App\Billing\Payer;
use App\Billing\Service;


/**
 * App\BusinessChain
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $address1
 * @property string|null $address2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string $country
 * @property string|null $phone1
 * @property string|null $phone2
 * @property bool $scheduling
 * @property bool $enable_schedule_groups
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Business[] $businesses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Caregiver[] $caregivers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OfficeUser[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain wherePhone1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain wherePhone2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereScheduling($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereZip($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CaregiverApplication[] $caregiverApplications
 */
class BusinessChain extends AuditableModel
{

    protected $table = 'business_chains';
    protected $guarded = ['id'];
    protected $orderedColumn = 'name';
    protected $casts = [
        'scheduling' => 'bool',
        'enable_schedule_groups' => 'bool',
    ];

    ////////////////////////////////////
    //// Static Methods
    ////////////////////////////////////

    public static function generateSlug($name)
    {
        return str_slug(
            str_replace('&', ' and ', $name)
        );
    }

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    public function businesses()
    {
        return $this->hasMany(Business::class, 'chain_id');
    }

    public function caregivers()
    {
        return $this->belongsToMany(Caregiver::class, 'chain_caregivers', 'chain_id')
            ->withTimestamps();
    }

    public function caregiverApplications()
    {
        return $this->hasMany(CaregiverApplication::class, 'chain_id');
    }

    public function users()
    {
        return $this->hasMany(OfficeUser::class, 'chain_id');
    }

    public function fields()
    {
        return $this->hasMany(CustomField::class, 'chain_id');
    }

    public function referralSources()
    {
        return $this->hasMany(ReferralSource::class, 'chain_id');
    }

    /**
     * Get the Businesses StatusAliases relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function statusAliases()
    {
        return $this->hasMany(StatusAlias::class, 'chain_id')->orderBy('name');
    }

    /**
     * Get the Payers relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function payers()
    {
        return $this->hasMany(Payer::class, 'chain_id');
    }

    /**
     * Get the Services relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function services()
    {
        return $this->hasMany(Service::class, 'chain_id');
    }

    /**
     * Get the chain's deactivation reasons relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deactivationReasons()
    {
        return $this->hasMany(DeactivationReason::class, 'chain_id')
            ->ordered();
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    /**
     * Get all deactivation reasons including factory defaults.
     *
     * @return mixed
     */
    public function getAllDeactivationReasonsAttribute()
    {
        return $this->deactivationReasons
            ->merge(DeactivationReason::whereNull('chain_id')->get())
            ->values();
    }

    /**
     * Get the Client deactivation reasons.
     *
     * @return mixed
     */
    public function getClientDeactivationReasonsAttribute()
    {
        return DeactivationReason::whereNull('chain_id')
            ->where('type', 'client')
            ->get()
            ->merge($this->deactivationReasons()
                ->where('type', 'client')
                ->get())
            ->values();
    }

    /**
     * Get the Caregiver deactivation reasons.
     *
     * @return mixed
     */
    public function getCaregiverDeactivationReasonsAttribute()
    {
        return DeactivationReason::whereNull('chain_id')
            ->where('type', 'caregiver')
            ->get()
            ->merge($this->deactivationReasons()
                ->where('type', 'caregiver')
                ->get())
            ->values();
    }

    /**
     * Assign a Caregiver to the chain and also all of the office locations.
     *
     * @param \App\Caregiver $caregiver
     * @return bool
     */
    public function assignCaregiver(Caregiver $caregiver) : bool
    {
        if (! $this->caregivers()->where('caregiver_id', $caregiver->id)->exists()) {
            if (! $this->caregivers()->save($caregiver)) {
                return false;
            }
        }

        return $caregiver->ensureBusinessRelationships($this);
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    public function getCaregiverApplicationUrl()
    {
        return route('business_chain_routes.apply', ['slug' => $this->slug]);
    }

    /**
     * Get a list of OfficeUser's notifiable User objects
     * that should be sent notifications.
     *
     * @return array|Collection
     */
    public function notifiableUsers()
    {
        return $this->users()->with(['user', 'user.notificationPreferences'])
            ->whereHas('user', function ($q) {
                $q->where('active', true);
            })
            ->get()
            ->pluck('user');
    }
}