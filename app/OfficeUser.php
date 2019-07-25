<?php
namespace App;

use App\Contracts\BelongsToChainsInterface;
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
 */
class OfficeUser extends AuditableModel implements UserRole, BelongsToChainsInterface
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
    protected $fillable = ['chain_id', 'default_business_id', 'timezone'];

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
    public function getTimezone()
    {
        if (! empty($this->timezone)) {
            return $this->timezone;
        }
        
        return $this->businesses->first()->timezone ?? 'America/New_York';
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
