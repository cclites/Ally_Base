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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftIssue whereCaregiverInjury($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftIssue whereClientInjury($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftIssue whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftIssue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftIssue whereShiftId($value)
 * @mixin \Eloquent
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftIssue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftIssue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftIssue query()
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

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding;

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|\Illuminate\Database\Eloquent\Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        return [
            'comments' => $faker->sentence,
        ];
    }
}
