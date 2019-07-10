<?php
namespace App\Traits;

use App\Address;
use App\Billing\Payments\Methods\BankAccount;
use App\Billing\Payments\Methods\CreditCard;
use App\EmergencyContact;
use App\PhoneNumber;
use App\User;
use App\Document;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\DeactivationReason;
use App\UserNotificationPreferences;
use App\SetupStatusHistory;
use org\apache\maven\POM\_4_0_0\Build;

trait IsUserRole
{
    use HiddenIdTrait;
    use HasAddressesAndNumbers;
    use SoftDeletes;

    /**
     * IsUserRole constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->alwaysIncludeUserRelationship();
        $this->appendAttributesToRoleModel();
        $this->appendSoftDeletesToFillable();
    }

    public static function bootIsUserRole()
    {
        static::deleting(function(self $obj) {
            $obj->addDeletedSuffixToEmail();
        });

        static::restoring(function(self $obj) {
            return $obj->restoreOriginalEmail();
        });
    }

    protected function alwaysIncludeUserRelationship()
    {
        if (empty($this->with)) $this->with = ['user'];
    }

    protected function appendAttributesToRoleModel()
    {
        $this->append(['firstname', 'lastname', 'email', 'username', 'date_of_birth', 'name', 'nameLastFirst', 'masked_name', 'gender', 'active', 'avatar', 'inactive_at', 'created_at', 'updated_at', 'deactivation_reason_id', 'reactivation_date', 'status_alias_id', 'setup_status']);
    }

    ///////////////////////////////////////////
    /// Soft Delete Management
    ///////////////////////////////////////////

    /**
     * Append deleted_at to the fillable property to make sure this attribute is updated on the Role Model
     */
    protected function appendSoftDeletesToFillable()
    {
        if (!in_array('deleted_at', $this->fillable)) {
            $this->fillable[] = 'deleted_at';
        }
    }

    /**
     * Change the user's email on deletion to allow user to be re-entered under another instance or client type (soft delete re-entry support)
     */
    public function addDeletedSuffixToEmail()
    {
        $newEmail = $this->user->email . '-deleted-' . time();
        $this->user->update(['email' => $newEmail]);
    }

    /**
     * Extend restore to check for a duplicate email and restore the email back to the original state
     *
     * @return bool
     */
    public function restoreOriginalEmail()
    {
        // Get original email
        $deletedEmail = $this->user->email;
        $deletedPosition = strpos($deletedEmail, '-deleted-');
        if ($deletedPosition) {
            $originalEmail = substr($deletedEmail, 0, $deletedPosition);
        }

        // Check for duplicate
        if (User::where('email', $originalEmail)->exists()) {
            return false;
        }

        if ($return = parent::restore()) {
            $this->user->update(['email' => $originalEmail]);
        }
        return $return;
    }

    /**
     * Forward the magic getter to the related User model if property is not found in the Role model
     *
     * @param $name
     * @return null
     */
    public function __get($name) {
        $parentValue = parent::__get($name);
        if ($parentValue === null) {
            if (isset($this->attributes[$this->primaryKey])) return $this->user->$name ?? null;
        }
        return $parentValue;
    }

    /**
     * Get the name of this Role (e.g. App\Client returns Client)
     *
     * @return string
     */
    public function getRoleType()
    {
        return snake_case(class_basename(get_called_class()));
    }

    ///////////////////////////////////////////
    /// Related User
    ///////////////////////////////////////////

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }

    ///////////////////////////////////////////
    /// Name Concatenation Forwarders
    ///////////////////////////////////////////

    public function name(): string
    {
        return $this->user->name();
    }

    public function nameLastFirst(): string
    {
        return $this->user->nameLastFirst();
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    public function getActiveAttribute()
    {
        return $this->user->active;
    }

    public function getCreatedAtAttribute()
    {
        return $this->user->created_at;
    }

    public function getUpdatedAtAttribute()
    {
        return $this->user->updated_at;
    }

    public function getInActiveAtAttribute()
    {
        return $this->user->inactive_at;
    }

    public function getGenderAttribute()
    {
        return $this->user->gender;
    }

    public function getFirstNameAttribute()
    {
        return $this->user->firstname;
    }

    public function getLastNameAttribute()
    {
        return optional($this->user)->lastname;
    }

    public function getEmailAttribute()
    {
        return $this->user->email;
    }

    public function getUsernameAttribute()
    {
        return $this->user->username;
    }

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
        return $this->user->masked_name;
    }

    public function getDateOfBirthAttribute()
    {
        return optional($this->user)->date_of_birth;
    }

    public function getAvatarAttribute()
    {
        if ($this->user->avatar) {
            return \Storage::disk('public')->url($this->user->avatar);
        } else {
            return '/images/default-avatar.png';
        }
    }

    public function setAvatarAttribute($value)
    {
        if (empty($value) || $value == '/images/default-avatar.png') {
            $this->attributes['avatar'] = null;
            return;
        }

        if (starts_with($value, config('app.url'))) {
            return;
        }

        $base64Data = str_replace('data:image/png;base64,', '', $value);
        $base64Data = str_replace(' ', '+', $base64Data);

        $filename = 'avatars/' . md5($this->id . uniqid() . microtime()) . '.png';

        if (\Storage::disk('public')->put($filename, base64_decode($base64Data))) {
            $this->attributes['avatar'] = $filename;
        }
    }

    public function getReactivationDateAttribute()
    {
        return optional($this->user->reactivation_date)->toDateTimeString();
    }

    public function getDeactivationReasonIdAttribute()
    {
        return $this->user ? $this->user->deactivation_reason_id : null;
    }

    public function getStatusAliasIdAttribute()
    {
        return $this->user ? $this->user->status_alias_id : null;
    }

    public function getSetupStatusAttribute()
    {
        return $this->user ? $this->user->setup_status : null;
    }

    function getHic(): ?string
    {
        if ($this->getRoleType() === 'client') {
            return $this->hic;
        }

        return null;
    }

    function getBirthdate(): ?string
    {
        if ($this->getRoleType() === 'client') {
            return $this->date_of_birth;
        }

        return null;
    }
    ///////////////////////////////////////////
    /// Attribute Input Handling
    ///////////////////////////////////////////

    /**
     * Simplifies the fill process to avoid checking against guarded attributes in the Role model
     * This is needed because $fillable is used to define role attributes which the rest being forwarded to the related User model
     *
     * @param array $attributes
     * @return $this
     */
    public function fill(array $attributes = [])
    {
        foreach($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * Overridden Save Method to save $fillable attributes to the Role Model with the remaining attributes forwarded to the related User Model
     *
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
    public function save(array $options = [])
    {
        $this->setIncrementing(false);

        $role_attributes = array_intersect_key($this->attributes, array_flip($this->fillable));
        $user_attributes = array_diff_key($this->attributes, array_flip($this->fillable));

        if ($this->id) {
            $user = $this->user;
            $user->update($user_attributes);
        }
        else {
            $user = User::forceCreate(array_merge(
                $user_attributes,
                ['role_type' => $this->getRoleType()]
            ));
        }

        if (!$user) {
            throw new \Exception('Unable to create user from role model.');
        }

        $this->attributes = array_merge(
            $role_attributes,
            ['id' => $user->id]
        );

        return parent::save($options);
    }

    ///////////////////////////////////////////
    /// Forwarded Relationship Methods
    /// (Phone numbers and addresses are in HasAddressesAndNumbers)
    ///////////////////////////////////////////

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class, 'user_id', 'id');
    }

    public function creditCards()
    {
        return $this->hasMany(CreditCard::class, 'user_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'user_id', 'id');
    }

    /**
     * Get the user notification preferences relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function notificationPreferences()
    {
        return $this->hasMany(UserNotificationPreferences::class, 'user_id', 'id');
    }

    public function deactivationReason()
    {
        return $this->hasOne(DeactivationReason::class, 'id', 'deactivation_reason_id');
    }

    /**
     * Get the user's setup status history relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function setupStatusHistory()
    {
        return $this->hasMany(SetupStatusHistory::class, 'user_id', 'id');
    }
    
    ////////////////////////////////////
    //// Query Scopes
    ////////////////////////////////////

    /**
     * Returns only active Clients.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $builder)
    {
        return $builder->whereHas('user', function($q) { $q->where('active', 1); });
    }

    /**
     * Returns only users with real email addresses
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeHasEmail(Builder $builder)
    {
        return $builder->whereHas('user', function(Builder $q) {
            $q->whereNotNull('email')->where('email', 'NOT LIKE', '%@noemail.allyms.com');
        });
    }

    /**
     * Returns only users without email addresses
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeDoesntHaveEmail(Builder $builder)
    {
        return $builder->whereHas('user', function(Builder $q) {
            $q->whereNull('email')->orWhere('email', 'LIKE', '%@noemail.allyms.com');
        });
    }

    /**
     * Search users by email, supports wildcards (%@email.com%)
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string|null $email
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereEmail(Builder $builder, string $email = null)
    {
        return $builder->whereHas('user', function($q) use ($email) {
            $q->where('email', 'LIKE', $email);
        });
    }

    /**
     * Search users by first and last name, supports wildcards (John%).  Set either parameter null to skip.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string|null $firstname
     * @param $string|null lastname
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereName(Builder $builder, string $firstname = null, string $lastname = null)
    {
        return $builder->whereHas('user', function($q) use ($firstname, $lastname) {
            if ($firstname !== null) $q->where('firstname', 'LIKE', $firstname);
            if ($lastname !== null) $q->where('lastname', 'LIKE', $lastname);
        });
    }

    /**
     * Add a query scope "ordered()" to centralize the control of sorting order of model results in queries
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string|null $direction
     */
    public function scopeOrdered(Builder $builder, string $direction = null)
    {
        $this->scopeOrderByName($builder, $direction ?? 'ASC');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string $direction
     */
    public function scopeOrderByName(Builder $builder, $direction = 'ASC')
    {
        $builder->join('users', 'users.id', '=', $this->table . '.id')
            ->orderBy('users.lastname', $direction)
            ->orderBy('users.firstname', $direction);
    }
}
