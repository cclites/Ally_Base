<?php
namespace App;

use App\Contracts\BelongsToChainsInterface;
use App\Contracts\UserRole;
use App\Traits\BelongsToBusinesses;
use App\Traits\BelongsToOneChain;
use App\Traits\IsUserRole;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\OfficeUser
 *
 * @property int $id
 * @property string|null $deleted_at
 * @property int|null $chain_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Address[] $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\PaymentMethods\BankAccount[] $bankAccounts
 * @property-read \App\BusinessChain|null $businessChain
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Business[] $businesses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\PaymentMethods\CreditCard[] $creditCards
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

    protected $table = 'office_users';
    public $timestamps = false;
    public $fillable = ['chain_id'];

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

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    /**
     * Return an array of business IDs the entity is attached to
     *
     * @return array
     */
    public function getBusinessIds()
    {
        return $this->businesses->pluck('id')->toArray();
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
            $q->whereIn('id', $businessIds);
        });
    }
}
