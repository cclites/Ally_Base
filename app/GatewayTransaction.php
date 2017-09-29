<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GatewayTransaction extends Model
{
    protected $table = 'gateway_transactions';
    protected $guarded = ['id'];

}
