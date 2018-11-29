<?php
namespace App;

/**
 * App\BusinessChain
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $address1
 * @property string|null $address2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string $country
 * @property string|null $phone1
 * @property string|null $phone2
 * @property int $scheduling
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Business[] $businesses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Caregiver[] $caregivers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OfficeUser[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain wherePhone1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain wherePhone2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereScheduling($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessChain whereZip($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CaregiverApplication[] $caregiverApplications
 */
class BusinessChain extends AuditableModel
{

    protected $table = 'business_chains';
    protected $guarded = ['id'];
    protected $orderedColumn = 'id';

    ////////////////////////////////////
    //// Static Methods
    ////////////////////////////////////

    public static function generateSlug($name)
    {
        return str_slug(
            str_replace('&', ' and ', $name)
        );
    }

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    public function businesses()
    {
        return $this->hasMany(Business::class, 'chain_id');
    }

    public function caregivers()
    {
        return $this->belongsToMany(Caregiver::class, 'chain_caregivers', 'chain_id')
            ->withTimestamps();
    }

    public function caregiverApplications()
    {
        return $this->hasMany(CaregiverApplication::class, 'chain_id');
    }

    public function users()
    {
        return $this->hasMany(OfficeUser::class, 'chain_id');
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    public function getCaregiverApplicationUrl()
    {
        return route('business_chain_routes.apply', ['slug' => $this->slug]);
    }
}