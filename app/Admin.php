<?php

namespace App;

use App\Contracts\UserRole;
use App\Traits\BelongsToBusinesses;
use App\Traits\IsUserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Admin
 *
 * @property int $id
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Address[] $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Payments\Methods\BankAccount[] $bankAccounts
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
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Admin active()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Admin forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Admin forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Admin orderByName($direction = 'ASC')
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Admin ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Admin whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Admin whereEmail($email = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Admin whereName($firstname = null, $lastname = null)
 * @mixin \Eloquent
 * @property-read mixed $created_at
 * @property-read mixed $masked_name
 * @property-read mixed $updated_at
 * @property-read \App\PhoneNumber $smsNumber
 */
class Admin extends AuditableModel implements UserRole
{
    use BelongsToBusinesses;
    use IsUserRole;

    protected $table = 'admins';
    public $timestamps = false;
    public $fillable = [];

    /**
     * Compatibility with User::getBusinessIds(),  Admin has access to all businesses
     *
     * @return array
     */
    public function getBusinessIds()
    {
        return Business::pluck('id')->toArray();
    }

    /**
     * A query scope for filtering results by related business IDs
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $businessIds
     * @return void
     */
    public function scopeForBusinesses(Builder $builder, array $businessIds)
    {
        return;
    }

    function getAddress(): ?Address
    {
        return null;
    }

    function getPhoneNumber(): ?PhoneNumber
    {
        return null;
    }

    /**
     * Get the admin user's timezone
     *
     * @return string
     */
    public function getTimezone()
    {
        return 'America/New_York';
    }
}
