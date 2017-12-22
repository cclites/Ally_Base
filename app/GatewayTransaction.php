<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
