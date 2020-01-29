<?php
namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;
use Carbon\Carbon;

/**
 * App\SystemException
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $reference_url
 * @property string|null $reference_type
 * @property string|null $reference_id
 * @property string|null $acknowledged_at
 * @property int|null $acknowledged_by
 * @property int $business_id
 * @property string|null $notes
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\User|null $acknowledger
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business $business
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $reference
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException notAcknowledged()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereAcknowledgedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereAcknowledgedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereReferenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereReferenceUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException query()
 */
class SystemException extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    protected $guarded = ['id'];
    protected $orderedColumn = 'id';

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function acknowledger()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function reference()
    {
        return $this->morphTo('reference');
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    public function scopeNotAcknowledged($query)
    {
        return $query->whereNull('acknowledged_at');
    }

    public function acknowledge($note = '', $user_id = null)
    {
        if (!$user_id) $user_id = \Auth::id();
        return $this->update([
            'acknowledged_at' => Carbon::now(),
            'acknowledged_by' => $user_id,
            'notes' => $note,
        ]);
    }

}