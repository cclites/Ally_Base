<?php
namespace App;

/**
 * App\Import
 *
 * @property int $id
 * @property string|null $name
 * @property int $user_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Import whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Import whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Import whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Import whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Import whereUserId($value)
 * @mixin \Eloquent
 */
class Import extends AuditableModel
{
    protected $table = 'imports';
    protected $fillable = [
        'name',
        'type',
        'user_id',
    ];

    public function shifts()
    {
        return $this->hasMany(Shift::class, 'import_id', 'id');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'import_id', 'id');
    }
}
