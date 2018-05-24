<?php
namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\CaregiverLicense
 *
 * @property int $id
 * @property int $caregiver_id
 * @property string $name
 * @property string $description
 * @property string $expires_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Caregiver $caregiver
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CaregiverLicense extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

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

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    public function isExpired()
    {
        $expireDate = new Carbon($this->expires_at);
        return ($expireDate < Carbon::now());
    }
}