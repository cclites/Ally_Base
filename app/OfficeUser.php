<?php
namespace App;

use App\Businesses\Timezone;
use App\Contracts\BelongsToChainsInterface;
use App\Contracts\HasTimezone;
use App\Contracts\UserRole;
use App\Traits\BelongsToBusinesses;
use App\Traits\BelongsToOneChain;
use App\Traits\IsUserRole;
use Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\OfficeUser
 *
 * @property int $id
 * @property string|null $deleted_at
 * @property int|null $chain_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Address[] $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Payments\Methods\BankAccount[] $bankAccounts
 * @property-read \App\BusinessChain|null $businessChain
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Business[] $businesses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Payments\Methods\CreditCard[] $creditCards
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Document[] $documents
 * @property-read mixed $active
 * @property mixed $avatar
 * @property-read mixed $date_of_birth
 * @property-read mixed $email
 * @property-read mixed $first_name
 * @property-read mixed $gender
 * @property-read mixed $in_active_at
 * @property-read mixed $last_name
 * @property-read mixed $name
 * @property-read mixed $name_last_first
 * @property-read mixed $username
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PhoneNumber[] $phoneNumbers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Task[] $tasks
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser active()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser forAuthorizedChain(\App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser forChains($chains)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser orderByName($direction = 'ASC')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser whereChainId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser whereEmail($email = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser whereName($firstname = null, $lastname = null)
 * @mixin \Eloquent
 * @property-read mixed $created_at
 * @property-read mixed $masked_name
 * @property-read mixed $updated_at
 * @property-read \App\PhoneNumber $smsNumber
 * @property int|null $default_business_id
 * @property string $timezone
 * @property int $views_reports
 * @property-read int|null $addresses_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Client[] $assignedClients
 * @property-read int|null $assigned_clients_count
 * @property-read int|null $audits_count
 * @property-read int|null $bank_accounts_count
 * @property-read int|null $businesses_count
 * @property-read int|null $credit_cards_count
 * @property-read \App\DeactivationReason $deactivationReason
 * @property-read \App\Business|null $defaultBusiness
 * @property-read int|null $documents_count
 * @property-read mixed $deactivation_reason_id
 * @property-read mixed $initialed_name
 * @property-read mixed $reactivation_date
 * @property-read mixed $setup_status
 * @property-read mixed $status_alias_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\UserNotificationPreferences[] $notificationPreferences
 * @property-read int|null $notification_preferences_count
 * @property-read int|null $phone_numbers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SetupStatusHistory[] $setupStatusHistory
 * @property-read int|null $setup_status_history_count
 * @property-read \App\StatusAlias $statusAlias
 * @property-read int|null $tasks_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser doesntHaveEmail()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser hasEmail()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser inactive()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser query()
 */
class OfficeUser extends AuditableModel implements UserRole, BelongsToChainsInterface, HasTimezone
{
    use IsUserRole, BelongsToBusinesses, BelongsToOneChain;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'office_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['chain_id', 'default_business_id', 'timezone', 'views_reports'];

    /**
     * The notification classes related to this user role.
     *
     * @return array
     */
    public static $availableNotifications = [
        \App\Notifications\Business\DeclinedVisit::class, // TODO: implement trigger
        \App\Notifications\Business\CaregiverAvailable::class, // TODO: implement trigger
        \App\Notifications\Business\UnverifiedShift::class,
        \App\Notifications\Business\CertificationExpiring::class,
        \App\Notifications\Business\CertificationExpired::class,
        \App\Notifications\Business\ApplicationSubmitted::class,
        \App\Notifications\Business\ManualTimesheet::class,
        \App\Notifications\Business\NewSmsReply::class,
        \App\Notifications\Business\FailedCharge::class, // TODO: implement trigger
        \App\Notifications\Business\ClientBirthday::class,
        \App\Notifications\Business\NoProspectContact::class, // TODO: implement trigger
        \App\Notifications\Business\OpenShiftRequested::class,
    ];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_office_users');
    }

    /**
     * Get the user's created tasks relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'creator_id');
    }

    /**
     * Get the default business relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\
    */
    public function defaultBusiness()
    {
        return $this->belongsTo(Business::class, 'default_business_id', 'id');
    }


    /**
     * first pass at the ability for features to be enabled/disabled based upon whether or not any of the associated businesses have the feature..
     * 
     */
    public function businessesWithOpenShiftsFeature()
    {
        $businesses = collect();

        foreach( $this->businesses as $business ){

            if( $business->has_open_shifts ) $businesses->push( $business );
        }

        return $businesses;
    }

    /**
     * 
     */
    public function getHasAccessToOpenShiftsFeatureAttribute()
    {

        return $this->businessesWithOpenShiftsFeature()->count() > 0;
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    public function getDefaultBusiness()
    {
        if (empty($this->defaultBusiness)) {
            return $this->businesses->first();
        }

        return $this->defaultBusiness;
    }

    function getAddress(): ?Address
    {
        return $this->addresses()->first();
    }

    function getPhoneNumber(): ?PhoneNumber
    {
        return $this->phoneNumbers()->first();
    }

    /**
     * Return an array of business IDs the entity is attached to
     *
     * @return array
     */
    public function getBusinessIds()
    {
        return $this->businesses->pluck('id')->toArray();
    }

    /**
     * Get the office user's timezone
     *
     * @return string
     */
    public function getTimezone() : string
    {
        if (! empty($this->timezone)) {
            return $this->timezone;
        }

        if ($business = $this->businesses->first()) {
            return Timezone::getTimezone($business->id);
        }

        return config('ally.local_timezone');
    }

    /**
     * Get the office user's assigned clients (clients they are the service coordinator for).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function assignedClients()
    {
        return $this->hasMany(Client::class, 'services_coordinator_id', 'id');
    }

    ////////////////////////////////////
    //// Query Scopes
    ////////////////////////////////////

    /**
     * A query scope for filtering results by related business IDs
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $businessIds
     * @return void
     */
    public function scopeForBusinesses(Builder $builder, array $businessIds)
    {
        $builder->whereHas('businesses', function($q) use ($businessIds) {
            $q->whereIn('business_id', $businessIds);
        });
    }
}
