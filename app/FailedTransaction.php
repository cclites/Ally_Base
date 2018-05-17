<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class FailedTransaction extends Model implements Auditable
{
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

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
