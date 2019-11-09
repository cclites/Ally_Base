<?php
namespace App;


use App\Traits\ScrubsForSeeding;

/**
 * App\ScheduleNote
 *
 * @property int $id
 * @property string $note
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Schedule[] $schedules
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScheduleNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScheduleNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScheduleNote whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScheduleNote whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ScheduleNote extends AuditableModel
{

    protected $table = 'schedule_notes';
    protected $guarded = ['id'];

    public function __toString()
    {
        return $this->note;
    }

    public function schedules() {
        return $this->hasMany(Schedule::class, 'note_id');
    }

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use ScrubsForSeeding;

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast) : array
    {
        return [
            'note' => $faker->sentence,
        ];
    }
}
