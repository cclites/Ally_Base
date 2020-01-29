<?php
namespace App;


/**
 * App\ShiftActivity
 *
 * @property int $id
 * @property int $shift_id
 * @property int|null $activity_id
 * @property string|null $other
 * @property int $completed
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftActivity whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftActivity whereCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftActivity whereOther($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftActivity whereShiftId($value)
 * @mixin \Eloquent
 * @property-read int|null $audits_count
 * @property-read \App\Shift $shift
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftActivity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftActivity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftActivity query()
 */
class ShiftActivity extends AuditableModel
{

    protected $table = 'shift_activities';
    public $timestamps = false;
    protected $guarded = ['id'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    /**
     * Get the related Shift.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

}
