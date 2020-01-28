<?php
namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;

/**
 * App\NoteTemplate
 *
 * @property int $id
 * @property string $short_name
 * @property int $active
 * @property string $note
 * @property int $created_by
 * @property int $business_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business $business
 * @property-read \App\User $creator
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NoteTemplate forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NoteTemplate forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NoteTemplate whereShortName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NoteTemplate whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NoteTemplate whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NoteTemplate whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NoteTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NoteTemplate whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NoteTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NoteTemplate whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NoteTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NoteTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NoteTemplate query()
 */
class NoteTemplate extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    protected $guarded = ['id'];
    protected $orderedColumn = 'short_name';

    public function business()
    {
        return $this->belongsTo('App\Business');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by');
    }
}
