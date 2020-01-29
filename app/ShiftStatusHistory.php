<?php
namespace App;

/**
 * App\ShiftStatusHistory
 *
 * @property int $id
 * @property int $shift_id
 * @property string|null $new_status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftStatusHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftStatusHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftStatusHistory whereNewStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftStatusHistory whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftStatusHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \App\Shift $shift
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftStatusHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftStatusHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftStatusHistory query()
 */
class ShiftStatusHistory extends AuditableModel
{
    protected $table = 'shift_status_history';
    protected $guarded = ['id'];

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}
