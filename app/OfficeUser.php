<?php

namespace App;

use App\Contracts\UserRole;
use App\Traits\IsUserRole;
use Illuminate\Database\Eloquent\Model;

/**
 * App\OfficeUser
 *
 * @property int $id
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Address[] $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\BankAccount[] $bankAccounts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Business[] $businesses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CreditCard[] $creditCards
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Document[] $documents
 * @property-read mixed $date_of_birth
 * @property-read mixed $email
 * @property-read mixed $first_name
 * @property-read mixed $last_name
 * @property-read mixed $name
 * @property-read mixed $name_last_first
 * @property-read mixed $username
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PhoneNumber[] $phoneNumbers
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser whereId($value)
 * @mixin \Eloquent
 */
class OfficeUser extends Model implements UserRole
{
    use IsUserRole;

    protected $table = 'office_users';
    public $timestamps = false;
    public $fillable = [];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_office_users');
    }
}
