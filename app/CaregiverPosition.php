<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\CaregiverPosition
 *
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CaregiverApplication[] $caregiverApplications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverPosition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverPosition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverPosition whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverPosition whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CaregiverPosition extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $guarded = ['id'];

    public function caregiverApplications()
    {
        return $this->hasMany(CaregiverApplication::class);
    }
}
