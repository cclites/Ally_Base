<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\ScheduleNote
 *
 * @property int $id
 * @property string $note
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Schedule[] $schedules
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScheduleNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScheduleNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScheduleNote whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScheduleNote whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ScheduleNote extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'schedule_notes';
    protected $guarded = ['id'];

    public function __toString()
    {
        return $this->note;
    }

    public function schedules() {
        return $this->hasMany(Schedule::class, 'note_id');
    }
}
