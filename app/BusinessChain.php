<?php
namespace App;

use App\Billing\Payer;
use App\Billing\Service;
use App\Traits\ScrubsForSeeding;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


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
 * @property int $calendar_week_start
 * @property-read int|null $audits_count
 * @property-read int|null $businesses_count
 * @property-read int|null $caregiver_applications_count
 * @property-read int|null $caregivers_count
 * @property-read \App\ChainClientTypeSettings $clientTypeSettings
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\DeactivationReason[] $deactivationReasons
 * @property-read int|null $deactivation_reasons_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ExpirationType[] $expirationTypes
 * @property-read int|null $expiration_types_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CustomField[] $fields
 * @property-read int|null $fields_count
 * @property-read mixed $all_deactivation_reasons
 * @property-read mixed $caregiver_deactivation_reasons
 * @property-read string $city_state_zip
 * @property-read mixed $client_deactivation_reasons
 * @property-read string|null $street_address
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Payer[] $payers
 * @property-read int|null $payers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ReferralSource[] $referralSources
 * @property-read int|null $referral_sources_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Service[] $services
 * @property-read int|null $services_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\StatusAlias[] $statusAliases
 * @property-read int|null $status_aliases_count
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain query()
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

    //Registry level 1099 defaults
    protected $chain1099Settings = [
        'medicaid_1099_default',
        'private_pay_1099_default',
        'other_1099_default',

        'medicaid_1099_send',
        'private_pay_1099_send',
        'other_1099_send',

        'medicaid_1099_from',
        'private_pay_1099_from',
        'other_1099_from',
    ];

    const OPEN_SHIFTS_DISABLED = 'off';
    const OPEN_SHIFTS_LIMITED = 'limited';
    const OPEN_SHIFTS_UNLIMITED = 'unlimited';

    ////////////////////////////////////
    //// Static Methods
    ////////////////////////////////////

    public static function generateSlug($name)
    {
        return Str::slug(
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

    /**
     * Get the expiration types relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function expirationTypes()
    {
        return $this->hasMany(ExpirationType::class, 'chain_id');
    }

    public function clientTypeSettings(){
        return $this->hasOne(ChainClientTypeSettings::class, 'business_chain_id', 'id');
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
     * 
     */
    public function getHasAccessToOpenShiftsFeatureAttribute()
    {
        return in_array( $this->open_shifts_setting, [ self::OPEN_SHIFTS_LIMITED, self::OPEN_SHIFTS_UNLIMITED ] );
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

    /**
     * @return string|null
     */
    public function getStreetAddressAttribute()
    {
        $fullAddress = $this->address1;

        if (!empty($this->address2)) {
            $fullAddress .= ' ' . $this->address2;
        }

        return $fullAddress;
    }

    /**
     * @return string
     */
    public function getCityStateZipAttribute(){
        return $this->city . ', ' . $this->state . ' ' . $this->country . ' ' . $this->zip;
    }

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use ScrubsForSeeding;

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        return [
            'address1' => $faker->streetAddress,
            'address2' => null,
            'phone1' => $faker->simple_phone,
            'phone2' => $faker->simple_phone,
        ];
    }
}