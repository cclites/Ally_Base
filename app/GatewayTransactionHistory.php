<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GatewayTransactionHistory extends Model
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
