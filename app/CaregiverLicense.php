<?php
namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\CaregiverLicense
 *
 * @property int $id
 * @property int $caregiver_id
 * @property string $name
 * @property string|null $description
 * @property \Carbon\Carbon $expires_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Caregiver $caregiver
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $chain_expiration_type_id
 * @property-read int|null $audits_count
 * @property-read \App\ExpirationType $defaultType
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereApplicable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereHasValidCaregiver()
 */
class CaregiverLicense extends AuditableModel
{
    protected $table = 'caregiver_licenses';
    protected $guarded = ['id'];
    public $dates = ['expires_at'];

    const INAPPLICABLE_DATE = '1337-01-01';

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    public function defaultType()
    {
        return $this->hasOne( ExpirationType::class, 'id', 'chain_expiration_type_id' );
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    public function isExpired()
    {
        $expireDate = new Carbon($this->expires_at);
        return ($expireDate < Carbon::now());
    }

    /**
     * Filter out Licenses that are artificially marked 'inapplicable'
     *  this artificial attribute sets the expiration date to the year 1337
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWhereApplicable($query)
    {
        return $query->where( 'expires_at', '>', self::INAPPLICABLE_DATE );
    }

    /**
     * Filter out Licenses that are artificially marked 'inapplicable'
     *  this artificial attribute sets the expiration date to the year 1337
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWhereHasValidCaregiver($query)
    {
        return $query->whereHas( 'caregiver' );
    }
    
    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding;

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|\Illuminate\Database\Eloquent\Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        $name = strtoupper($faker->randomLetter() . $faker->randomLetter() . $faker->randomLetter());
        return [
            'name' => $name,
            'description' => $name . ' Certification',
        ];
    }
}