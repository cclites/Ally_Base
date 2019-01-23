<?php
namespace App\Billing;

use App\AuditableModel;

/**
 * App\Billing\GatewayTransactionHistory
 *
 * @property int $id
 * @property int $internal_transaction_id
 * @property string $action
 * @property string $status
 * @property float $amount
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Billing\GatewayTransaction $transaction
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransactionHistory whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransactionHistory whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransactionHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransactionHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransactionHistory whereInternalTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransactionHistory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransactionHistory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GatewayTransactionHistory extends AuditableModel
{
    protected $table = 'gateway_transaction_history';
    protected $guarded = ['id'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function transaction()
    {
        return $this->belongsTo(GatewayTransaction::class, 'internal_transaction_id');
    }

    ////////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

}
