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

    public function history()
    {
        return $this->hasMany(GatewayTransactionHistory::class, 'internal_transaction_id');
    }

    public function lastHistory()
    {
        return $this->hasOne(GatewayTransactionHistory::class, 'internal_transaction_id')
            ->orderBy('created_at', 'DESC');
    }

    ////////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

}
