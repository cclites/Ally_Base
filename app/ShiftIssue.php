<?php
namespace App;

/**
 * App\ShiftIssue
 *
 * @property int $id
 * @property int $shift_id
 * @property int $client_injury
 * @property int $caregiver_injury
 * @property string|null $comments
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Shift $shift
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftIssue whereCaregiverInjury($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftIssue whereClientInjury($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftIssue whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftIssue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftIssue whereShiftId($value)
 * @mixin \Eloquent
 */
class ShiftIssue extends AuditableModel
{

    protected $table = 'shift_issues';
    public $timestamps = false;
    protected $guarded = ['id'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

}
