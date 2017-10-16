<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'firstname', 'lastname', 'date_of_birth', 'access_group_id', 'active'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function name()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function getNameAttribute()
    {
        return $this->name();
    }

    public function nameLastFirst()
    {
        return $this->lastname . ', ' . $this->firstname;
    }

    public function role()
    {
        if ($this->getRoleClass()) {
            return $this->hasOne($this->getRoleClass(), 'id', 'id');
        }
        return null;
    }

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

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    public function creditCards()
    {
        return $this->hasMany(CreditCard::class);
    }

    public function phoneNumbers()
    {
        return $this->hasMany(PhoneNumber::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function changePassword($password)
    {
        return $this->update(['password' => bcrypt($password)]);
    }
}
