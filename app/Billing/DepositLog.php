<?php
namespace App\Billing;

use App\BaseModel;

/**
 * \App\Billing\DepositLog
 *
 * @property int $id
 * @property string $batch_id
 * @property int|null $chain_id
 * @property int|null $deposit_id
 * @property string|null $payment_method_type
 * @property int|null $payment_method_id
 * @property string|null $exception
 * @property string|null $error_message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\BusinessChain|null $chain
 * @property-read \App\Billing\Deposit|null $deposit
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $method
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\DepositLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\DepositLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\DepositLog query()
 * @mixin \Eloquent
 */
class DepositLog extends BaseModel
{
    use PaymentLogFunctions;

    protected $table = 'deposit_log';
    public $guarded = ['id'];
    protected static $batchPrefix = "D";

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    public function deposit()
    {
        return $this->belongsTo(Deposit::class, 'deposit_id');
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    public function setDeposit(Deposit $deposit)
    {
        $this->deposit()->associate($deposit);
    }
}