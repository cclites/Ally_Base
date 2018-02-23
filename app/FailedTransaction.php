<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FailedTransaction extends Model
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
