<?php

namespace App;

use App\Billing\Payments\Methods\BankAccount;
use App\Billing\Payments\Methods\CreditCard;
use App\Contracts\BelongsToBusinessesInterface;
use App\Contracts\HasPaymentHold;
use App\Contracts\HasTimezone;
use App\Traits\BelongsToBusinesses;
use App\Traits\CanImpersonate;
use App\Traits\HasAddressesAndNumbers;
use App\Traits\HiddenIdTrait;
use App\Traits\PreventsDelete;
use App\SmsThread;
use App\Traits\ScrubsForSeeding;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use OwenIt\Auditing\Contracts\Auditable;
use Packages\MetaData\HasMetaData;
use App\PhoneNumber;
use Illuminate\Database\Eloquent\Model;

/**
 * App\User
 *
 * @property int $id
 * @property string $email
 * @property string|null $username
 * @property string $password
 * @property string|null $firstname
 * @property string|null $lastname
 * @property string|null $date_of_birth
 * @property string $role_type
 * @property int|null $access_group_id
 * @property int $active
 * @property string|null $inactive_at
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $welcome_email_sent_at
 * @property string|null $training_email_sent_at
 * @property string|null $gender
 * @property string|null $avatar
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Address[] $addresses
 * @property-read \App\Admin $admin
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Payments\Methods\BankAccount[] $bankAccounts
 * @property-read \App\Caregiver $caregiver
 * @property-read \App\Client $client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Payments\Methods\CreditCard[] $creditCards
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Document[] $documents
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Task[] $dueTasks
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\EmergencyContact[] $emergencyContacts
 * @property-read string $default_phone
 * @property-read mixed $name
 * @property-read mixed $name_last_first
 * @property-read \Illuminate\Database\Eloquent\Collection|\Packages\MetaData\MetaData[] $meta
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \App\OfficeUser $officeUser
 * @property-read \App\PaymentHold $paymentHold
 * @property-read \App\Contracts\UserRole $role
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PhoneNumber[] $phoneNumbers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Task[] $tasks
 * @property-read \App\SmsThread $smsThreads
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAccessGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmailSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereInactiveAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereMeta($key, $delimiter = null, $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRoleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User withMeta()
 * @property \App\User formatEmergencyContact()
 * @mixin \Eloquent
 * @property-read mixed $masked_name
 * @property-read \App\PhoneNumber $smsNumber
 */
class User extends Authenticatable implements HasPaymentHold, Auditable, BelongsToBusinessesInterface, HasTimezone
{
    use BelongsToBusinesses;
    use Notifiable;
    use PreventsDelete;
    use CanImpersonate;
    use HiddenIdTrait;
    use \App\Traits\HasPaymentHold;
    use \OwenIt\Auditing\Auditable;
    use HasAddressesAndNumbers;
    use HasMetaData;
    use ScrubsForSeeding { getScrubQuery as parentGetScrubQuery; }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'remember_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $appends = ['name', 'nameLastFirst'];

    protected $dates = ['reactivation_date'];

    ///////////////////////////////////////////
    /// Name Concatenation Methods
    ///////////////////////////////////////////

    public function name(): string
    {
        return trim($this->firstname . ' ' . $this->lastname);
    }

    public function nameLastFirst(): string
    {
        return trim($this->lastname . ', ' . $this->firstname);
    }

    ////////////////////////////////////
    //// Role Relationships
    ////////////////////////////////////

    /**
     * Get the role instance, compatible with any role but not compatible with eager loading and querying relations
     * Note: Use admin, caregiver, client, or officeUser methods for eager loading or querying
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|null
     */
    public function role()
    {
        if ($this->getRoleClass()) {
            return $this->hasOne($this->getRoleClass(), 'id', 'id');
        }
        return null;
    }

    public function admin()
    {
        return $this->hasOne(Admin::class, 'id', 'id');
    }

    public function adminNotes()
    {
        return $this->hasMany( UserAdminNote::class, 'subject_user_id', 'id' );
    }

    public function caregiver()
    {
        return $this->hasOne(Caregiver::class, 'id', 'id');
    }

    public function client()
    {
        return $this->hasOne(Client::class, 'id', 'id');
    }

    public function officeUser()
    {
        return $this->hasOne(OfficeUser::class, 'id', 'id');
    }

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    public function creditCards()
    {
        return $this->hasMany(CreditCard::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function emergencyContacts()
    {
        return $this->hasMany(EmergencyContact::class)
            ->orderBy('priority');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_user_id');
    }

    public function dueTasks()
    {
        return $this->hasMany(Task::class, 'assigned_user_id')
            ->whereNull('completed_at');
    }

    /**
     * Get the user notification preferences relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function notificationPreferences()
    {
        return $this->hasMany(UserNotificationPreferences::class);
    }

    /**
     * A user can have many SystemNotifications 
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function systemNotifications()
    {
        return $this->hasMany(SystemNotification::class);
    }
    
    /**
     * Get the user's setup status history relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function setupStatusHistory()
    {
        return $this->hasMany(SetupStatusHistory::class);
    }

    /**
     *  Get messages sent by user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function smsThreads(){
        return $this->hasMany(SmsThread::class);
    }
    
    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    public function getNameAttribute()
    {
        return $this->name();
    }

    public function getNameLastFirstAttribute()
    {
        return $this->nameLastFirst();
    }

    public function getMaskedNameAttribute()
    {
        $first = $this->firstname;
        if (mb_strlen($first) > 1) {
            $first = mb_substr($first, 0, 2) . str_repeat('*', mb_strlen($first) - 2);
        }

        $last = $this->lastname;
        if (mb_strlen($last) > 1) {
            $last = mb_substr($last, 0, 2) . str_repeat('*', mb_strlen($last) - 2);
        }
        return "$first $last";
    }

    /**
     * Get the default phone number for the user.
     *
     * @return string
     */
    public function getDefaultPhoneAttribute()
    {
        $phone = null;

        if ($this->phoneNumbers->where('type', 'primary')->count()) {
            $phone = $this->phoneNumbers->where('type', 'primary')->first();
        } elseif ($this->phoneNumbers->where('type', 'mobile')->count()) {
            $phone = $this->phoneNumbers->where('type', 'mobile')->first();
        } else {
            $phone = $this->phoneNumbers->first();
        }

        return empty($phone) ? '' : (string) $phone->number;
    }

    ///////////////////////////////////////////
    /// Query Scopes
    ///////////////////////////////////////////

    /**
     * A query scope for filtering results by related business IDs
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $businessIds
     * @return void
     */
    public function scopeForBusinesses(Builder $builder, array $businessIds)
    {
        $builder->where(function($query) use ($businessIds) {
            $query->whereHas('caregiver', function($q) use ($businessIds) {
                $q->forBusinesses($businessIds);
            })->orWhereHas('client', function($q) use ($businessIds) {
                $q->forBusinesses($businessIds);
            })->orWhereHas('officeUser', function($q) use ($businessIds) {
                $q->forBusinesses($businessIds);
            });
        });
    }

    /**
     * Get the clients that belong to the specified chains.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param int $chainId
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForChain($query, $chainId)
    {
        return $query->where(function ($q) use ($chainId) {
            $q->whereHas('client', function ($q) use ($chainId) {
                $q->forChain($chainId);
            })->orWhereHas('caregiver', function ($q) use ($chainId) {
                $q->forChains([$chainId]);
            })->orWhereHas('officeUser', function ($q) use ($chainId) {
                $q->forChains([$chainId]);
            });
        });
    }

    /**
     * Get users who's data matches the specified search filter.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string|null $searchFilter
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSearch($query, $searchFilter)
    {
        if (empty($searchFilter)) {
            return $query;
        }

        return $query->where(function ($q) use ($searchFilter) {
            $q->where('users.username', 'LIKE', "%$searchFilter%")
                ->orWhere('users.email', 'LIKE', "%$searchFilter%")
                ->orWhere('users.id', 'LIKE', "%$searchFilter%")
                ->orWhere('users.firstname', 'LIKE', "%$searchFilter%")
                ->orWhere('users.lastname', 'LIKE', "%$searchFilter%")
                ->orWhere('users.role_type', 'LIKE', "%$searchFilter%");
        });
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    /**
     * Get the age of a user based on the date of birth
     *
     * @return int|null
     */
    public function getAge()
    {
        if (!is_null($this->date_of_birth)) {
            return now()->diffInYears(Carbon::parse($this->date_of_birth));
        }
        return null;
    }

    public function getFormattedGenderAttribute()
    {
        switch( strtolower( $this->gender ) ){

            case 'm':

                return 'Male';
                break;
            case 'f':

                return 'Female';
                break;
            default:

                return null;
        }
    }

    /**
     * Return the fully-qualified name of the role class
     *
     * @param null $type
     * @return null|string
     */
    public function getRoleClass($type = null)
    {
        if (! $type) {
            $type = $this->role_type;
        }

        switch ($type) {
            case 'admin':
                return Admin::class;
            case 'caregiver':
                return Caregiver::class;
            case 'client':
                return Client::class;
            case 'office_user':
                return OfficeUser::class;
        }

        return null;
    }

    /**
     * Change the user's password
     *
     * @param $password
     * @return bool
     */
    public function changePassword($password)
    {
        return $this->update(['password' => bcrypt($password)]);
    }

    /**
     * Return an array of business IDs the entity is attached to
     *
     * @return array
     */
    public function getBusinessIds()
    {
        return $this->role->getBusinessIds();
    }

    /**
     * Get the owning business chain for the user based on
     * their role_type.
     * Warning: will cause bad n+1 issues if relationships are
     * not pre-loaded in the query.
     *
     * @return \App\BusinessChain|null
     */
    public function getChain()
    {
        switch ($this->role_type) {
            case 'caregiver':
                return optional(optional($this->caregiver)->businessChains)->first();
            case 'client':
                return optional(optional($this->client)->business)->businessChain;
            case 'office_user':
                return optional($this->officeUser)->businessChain;
            default:
                return null;
        }
    }

    /**
     * Get the default notification preferences for the given notification.
     *
     * @param string $key
     * @return UserNotificationPreferences
     */
    public function getDefaultNotificationPreferences(string $key) : UserNotificationPreferences
    {
        $prefs = new UserNotificationPreferences();
        $prefs->mail = false;
        $prefs->sms = false;
        $prefs->system = false;

        switch ($this->role_type) {
            case 'office_user':
                $prefs->system = true;
                break;
            case 'caregiver':
            case 'client':
            default:
                break;
        }

        return $prefs;
    }

    /**
     * Determine if the system shound notify the user for the given notification class and
     * notification method.
     *
     * @param string $notification
     * @param string $via
     * @return boolean
     */
    public function shouldNotify($notification, $via)
    {
        $preference = $this->notificationPreferences()->where('key', $notification)->first();
        if (! $preference) {
            $preference = $this->getDefaultNotificationPreferences($notification);
        }

        switch ($via) {
            case 'mail': 
                if (! $this->allow_email_notifications || empty($this->notification_email)) {
                    return false;
                }
                if (! $preference->email) {
                    return false;
                }
                break;

            case 'sms': 
                if (! $this->allow_sms_notifications || empty($this->notification_phone)) {
                    return false;
                }
                if (! $preference->sms) {
                    return false;
                }
                break;

            case 'system':
                if (! $this->allow_system_notifications || $this->role_type != 'office_user') {
                    return false;
                }
                if (! $preference->system) {
                    return false;
                }
                break;
        }

        return true;
    }

    /**
     * Get a collection of the available notification
     * types based on the current user role.
     *
     * @return Collection
     */
    public function getAvailableNotifications()
    {
        switch ($this->role_type) {
            case 'office_user':
                return collect(OfficeUser::$availableNotifications);
            case 'caregiver':
                return collect(Caregiver::$availableNotifications);
            case 'client':
                return collect(Client::$availableNotifications);
            default:
                return collect([]);
        }
    }

    /**
     * Sync notification data from request and create new preferences  
     * or update existing.
     *
     * @param array $data
     * @return void
     */
    public function syncNotificationPreferences(array $preferences) : void
    {
        foreach ($preferences as $key => $data) {
            $pref = $this->notificationPreferences()
                ->where('key', $key)
                ->first();

            if ($pref) {
                $pref->update($data);
            } else {
                $data['key'] = $key;
                $this->notificationPreferences()->create($data);
            }
        }
    }

    /**
     * Format emergency contact info
     *
     * @return string
     */
    public function formatEmergencyContact(){

        $record = $this->emergencyContacts->where('priority', 1)->first();

        if(filled($record)){
            return $record->name . "; " . $record->phone_number . "; " . $record->relationship;
        }

        return '';

    }

    /**
     * Get the user's Timezone.
     *
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->role->getTimezone();
    }

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************

    /**
     * Get the query used to identify records that will be scrubbed.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getScrubQuery() : Builder
    {
        return static::parentGetScrubQuery()
            ->where('role_type', '<>', 'admin');
    }

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
        $email = $faker->email;
        return [
            'date_of_birth' => $faker->date('Y-m-d', '-30 years'),
            'lastname' => $fast ? 'User' : $faker->lastName,
            'username' => $fast ? \DB::raw("CONCAT('user', id)") : $email,
            'email' => $fast ? \DB::raw("CONCAT('user', id, '@test.com')") : $email,
            'notification_email' => $fast ? \DB::raw("CONCAT('user', id, '@test.com')") : $email,
            'notification_phone' => $faker->simple_phone,
            'remember_token' => null,
        ];
    }
}
