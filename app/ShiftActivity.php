<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\ShiftActivity
 *
 * @property int $id
 * @property int $shift_id
 * @property int|null $activity_id
 * @property string|null $other
 * @property int $completed
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftActivity whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftActivity whereCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftActivity whereOther($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftActivity whereShiftId($value)
 * @mixin \Eloquent
 */
class ShiftActivity extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'shift_activities';
    public $timestamps = false;
    protected $guarded = ['id'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

}
