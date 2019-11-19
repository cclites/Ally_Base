<?php
namespace App\Billing;

use App\AuditableModel;
use App\Events\FailedTransactionFound;
use App\Events\FailedTransactionRecorded;
use App\Billing\GatewayTransactionHistory;

/**
 * App\Billing\GatewayTransaction
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
 * @property  int $routing_number
 * @property  int $account_number
 * @property string|null $response_text
 * @property string|null $response_data
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Billing\Deposit $deposit
 * @property-read \App\Billing\FailedTransaction $failedTransaction
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\GatewayTransactionHistory[] $history
 * @property-read \App\Billing\GatewayTransactionHistory $lastHistory
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $method
 * @property-read \App\Billing\Payment $payment
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\TransactionRefund[] $refunds
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\GatewayTransaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\GatewayTransaction whereAvsPass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\GatewayTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\GatewayTransaction whereCvvPass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\GatewayTransaction whereDeclined($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\GatewayTransaction whereGatewayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\GatewayTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\GatewayTransaction whereMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\GatewayTransaction whereMethodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\GatewayTransaction whereResponseData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\GatewayTransaction whereResponseText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\GatewayTransaction whereSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\GatewayTransaction whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\GatewayTransaction whereTransactionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\GatewayTransaction whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GatewayTransaction extends AuditableModel
{
    protected $table = 'gateway_transactions';
    protected $guarded = ['id'];
    protected $casts = [
        'amount' => 'float',
        'success' => 'bool',
        'declined' => 'bool',
        'cvs_pass' => 'bool',
        'avs_pass' => 'bool',
    ];

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

    public function refunds()
    {
        return $this->hasMany(TransactionRefund::class, 'refunded_transaction_id');
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

            if ($deposit = $this->deposit) {
                $deposit->markFailed();
            }

            if ($payment = $this->payment) {
                $payment->markFailed();
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

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding;
    use \App\Traits\ScrubsForSeeding { getScrubQuery as parentGetScrubQuery; }

    /**
     * Get the query used to identify records that will be scrubbed.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getScrubQuery() : \Illuminate\Database\Eloquent\Builder
    {
        return static::parentGetScrubQuery()->whereNotNull('routing_number')->orWhereNotNull('account_number');
    }

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
            'routing_number' => $faker->randomNumber(4),
            'account_number' => $faker->randomNumber(4),
        ];
    }
}
