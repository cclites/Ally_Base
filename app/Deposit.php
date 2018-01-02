<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Deposit
 *
 * @property int $id
 * @property string $deposit_type
 * @property int|null $caregiver_id
 * @property int|null $business_id
 * @property float|null $amount
 * @property string|null $transaction_id
 * @property string|null $transaction_code
 * @property int|null $success
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Business|null $business
 * @property-read \App\Caregiver|null $caregiver
 * @property-read mixed $week
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $method
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @property-read \App\GatewayTransaction|null $transaction
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereDepositType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereTransactionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Deposit extends Model
{
    protected $table = 'deposits';
    protected $guarded = ['id'];
    protected $appends = ['week'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function method()
    {
        return $this->morphTo();
    }

    public function transaction()
    {
        return $this->belongsTo(GatewayTransaction::class, 'transaction_id');
    }

    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'deposit_shifts');
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    public function getWeekAttribute()
    {
        if (!$this->created_at) {
            return null;
        }

        $date = $this->created_at->copy()->subWeek();
        return [
            'start' => $date->setIsoDate($date->year, $date->weekOfYear)->toDateString(),
            'end' => $date->setIsoDate($date->year, $date->weekOfYear, 7)->toDateString()
        ];
    }
}
