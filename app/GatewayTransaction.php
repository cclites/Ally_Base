<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\GatewayTransaction
 *
 * @property int $id
 * @property string $gateway_id
 * @property string $transaction_id
 * @property string $transaction_type
 * @property string|null $method_type
 * @property int|null $method_id
 * @property float $amount
 * @property int $success
 * @property int $declined
 * @property int|null $cvv_pass
 * @property int|null $avs_pass
 * @property string|null $response_text
 * @property string|null $response_data
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Deposit $deposit
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\GatewayTransactionHistory[] $history
 * @property-read \App\GatewayTransactionHistory $lastHistory
 * @property-read \App\Payment $payment
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereAvsPass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereCvvPass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereDeclined($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereGatewayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereMethodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereResponseData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereResponseText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereTransactionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GatewayTransaction extends Model
{
    protected $table = 'gateway_transactions';
    protected $guarded = ['id'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function payment()
    {
        return $this->hasOne(Payment::class, 'transaction_id');
    }

    public function deposit()
    {
        return $this->hasOne(Deposit::class, 'transaction_id');
    }

    public function history()
    {
        return $this->hasMany(GatewayTransactionHistory::class, 'internal_transaction_id');
    }

    public function lastHistory()
    {
        return $this->hasOne(GatewayTransactionHistory::class, 'internal_transaction_id')
            ->orderBy('created_at', 'DESC');
    }

    public function method()
    {
        return $this->morphTo();
    }

    ////////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

}
