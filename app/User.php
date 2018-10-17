<?php

namespace App;

use App\Contracts\HasPaymentHold;
use App\Traits\HasAddressesAndNumbers;
use App\Traits\HiddenIdTrait;
use App\Traits\PreventsDelete;
use Bizhub\Impersonate\Traits\CanImpersonate;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use OwenIt\Auditing\Contracts\Auditable;

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
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $email_sent_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Address[] $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\BankAccount[] $bankAccounts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CreditCard[] $creditCards
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Document[] $documents
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\EmergencyContact[] $emergencyContacts
 * @property-read mixed $name
 * @property-read mixed $name_last_first
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PhoneNumber[] $phoneNumbers
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAccessGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmailSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRoleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUsername($value)
 * @mixin \Eloquent
 * @property string|null $gender
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereGender($value)
 */
class User extends Authenticatable implements HasPaymentHold, Auditable
{
    use Notifiable;
    use PreventsDelete;
    use CanImpersonate;
    use HiddenIdTrait;
    use \App\Traits\HasPaymentHold;
    use \OwenIt\Auditing\Auditable;
    use HasAddressesAndNumbers;

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

    ///////////////////////////////////////////
    /// Name Concatenation Methods
    ///////////////////////////////////////////

    public function name()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function nameLastFirst()
    {
        return $this->lastname . ', ' . $this->firstname;
    }

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function role()
    {
        if ($this->getRoleClass()) {
            return $this->hasOne($this->getRoleClass(), 'id', 'id');
        }
        return null;
    }

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
        if (strlen($first) > 1) {
            $first = substr($first, 0, 2) . str_repeat('*', strlen($first) - 2);
        }

        $last = $this->lastname;
        if (strlen($last) > 1) {
            $last = substr($last, 0, 2) . str_repeat('*', strlen($last) - 2);
        }
        return "$first $last";
    }
    
    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    /**
     * Return the fully-qualified name of the role class
     *
     * @param null $type
     * @return null|string
     */
    public function getRoleClass($type = null)
    {
        if (!$type) $type = $this->role_type;

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

    public function officeUser()
    {
        return $this->hasOne('App\OfficeUser', 'id', 'id');
    }
}
