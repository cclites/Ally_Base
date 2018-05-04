<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\CaregiverApplicationStatus
 *
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CaregiverApplication[] $caregiverApplications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplicationStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplicationStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplicationStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplicationStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CaregiverApplicationStatus extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $guarded = ['id'];

    public function caregiverApplications()
    {
        return $this->hasMany(CaregiverApplication::class);
    }
}
