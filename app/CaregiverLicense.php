<?php
namespace App;

use Carbon\Carbon;

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
 */
class CaregiverLicense extends AuditableModel
{
    protected $table = 'caregiver_licenses';
    protected $guarded = ['id'];
    public $dates = ['expires_at'];

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
}