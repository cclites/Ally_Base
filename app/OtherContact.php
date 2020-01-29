<?php

namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\OtherContact
 *
 * @property int $id
 * @property string $firstname
 * @property string $lastname
 * @property int $business_id
 * @property string|null $email
 * @property string|null $title
 * @property string|null $company
 * @property string|null $phone
 * @property string|null $address1
 * @property string|null $address2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string|null $country
 * @property string|null $general_notes
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Business $business
 * @property-read mixed $name
 * @property-read mixed $name_last_first
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact whereGeneralNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact whereZip($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OtherContact query()
 */
class OtherContact extends Model implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    /**
     * The fields that are mass assignable
     *
     * @var array
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'business_id',
        'email',
        'title',
        'company',
        'phone',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'country',
        'general_notes',
    ];

    ////////////////////////////////////
    //// Relationships
    ////////////////////////////////////

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    ////////////////////////////////////
    //// Mutators
    ////////////////////////////////////

    public function getNameAttribute()
    {
        return $this->name();
    }

    public function getNameLastFirstAttribute()
    {
        return $this->nameLastFirst();
    }

    public function name()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function nameLastFirst()
    {
        return $this->lastname . ', ' . $this->firstname;
    }

    ////////////////////////////////////
    //// Query Scopes
    ////////////////////////////////////

    /**
     * Add a query scope "ordered()" to centralize the control of sorting order of model results in queries
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string|null $direction
     */
    public function scopeOrdered(Builder $builder, string $direction = null)
    {
        $builder->orderBy('lastname', $direction ?? 'ASC')
            ->orderBy('firstname', $direction ?? 'ASC');
    }

}
