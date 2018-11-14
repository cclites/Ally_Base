<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\CaregiverMeta
 *
 * @property int $id
 * @property int $caregiver_id
 * @property string $key
 * @property string|null $value
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverMeta whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverMeta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverMeta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverMeta whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverMeta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverMeta whereValue($value)
 * @mixin \Eloquent
 */
class CaregiverMeta extends Model
{
    protected $table = 'caregiver_meta';
    protected $fillable = ['key', 'value'];
}
