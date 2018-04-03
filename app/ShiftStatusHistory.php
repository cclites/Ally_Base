<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ShiftStatusHistory
 *
 * @property int $id
 * @property int $shift_id
 * @property string|null $new_status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftStatusHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftStatusHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftStatusHistory whereNewStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftStatusHistory whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftStatusHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ShiftStatusHistory extends Model
{
    protected $table = 'shift_status_history';
    protected $guarded = ['id'];
}
