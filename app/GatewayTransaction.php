<?php

namespace App;

use App\Events\FailedTransactionFound;
use App\Events\FailedTransactionRecorded;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

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
 * @property-read \Illuminate\Database\Eloquent\Model|\App\Contracts\ChargeableInterface $method
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
class GatewayTransaction extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

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

    public function failedTransaction()
    {
        return $this->hasOne(FailedTransaction::class, 'id');
    }

    public function method()
    {
        return $this->morphTo();
    }

    ////////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    /**
     * Add a failed_transactions record for a potential failure that needs acknowledgement
     *
     * @return bool
     */
    public function foundFailure()
    {
        // If the transaction has already been marked as failed, return
        if (! $this->success) {
            return true;
        }

        // If a failed transaction record already exists, return
        if ($this->failedTransaction) {
            return true;
        }

        // If a failed transaction record has already been marked successful, return
        if ($failedTransaction = $this->failedTransaction()->withTrashed()->first()) {
            $failedTransaction->restore();
            $failedTransaction->touch();
            return true;
        }

        $failure = FailedTransaction::create(['id' => $this->id]);
        event(new FailedTransactionFound($this));
        return $failure;
    }

    /**
     * Record a true failure (found failure was acknowledged and not discarded)
     *
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function recordFailure()
    {
        $return = \DB::transaction(function() {
            if (!$this->update(['success' => 0])) {
                throw new \Exception();
            }
            if ($this->failedTransaction) {
                $this->failedTransaction->delete();
            }
            return true;
        });

        if ($return) {
            event(new FailedTransactionRecorded($this));
            return true;
        }

        return false;
    }

    /**
     * Discard a found failure (not a true failure)
     *
     * @return mixed
     */
    function discardFailure() {
        // Just delete the record without doing anything else
        return $this->failedTransaction()->delete();
    }

    /**
     * Remove all holds related to the current transaction.
     *
     * @param [type] $event
     * @return void
     */
    public function removeHolds()
    {
        if ($this->payment) {
            if ($this->payment->client) {
                $this->payment->client->removeHold();
            }
            else if ($this->payment->business) {
                $this->payment->business->removeHold();
            }
        }

        elseif ($this->deposit) {
            if ($this->deposit->caregiver) {
                $this->deposit->caregiver->removeHold();
            }
            else if ($this->deposit->business) {
                $this->deposit->business->removeHold();
            }
        }
    }
}
