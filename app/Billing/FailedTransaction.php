<?php
namespace App\Billing;

use App\AuditableModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Billing\FailedTransaction
 *
 * @property int $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Billing\GatewayTransaction $transaction
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\FailedTransaction onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FailedTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FailedTransaction whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FailedTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\FailedTransaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\FailedTransaction withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\FailedTransaction withoutTrashed()
 * @mixin \Eloquent
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\FailedTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\FailedTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\FailedTransaction query()
 */
class FailedTransaction extends AuditableModel
{
    use SoftDeletes;

    protected $table = 'failed_transactions';
    protected $guarded = [];

    ///////////////////////////////////////////
    /// Relationships
    ///////////////////////////////////////////

    public function transaction()
    {
        return $this->belongsTo(GatewayTransaction::class, 'id');
    }

    ///////////////////////////////////////////
    /// Other
    ///////////////////////////////////////////
}
