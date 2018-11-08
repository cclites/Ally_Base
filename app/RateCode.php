<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RateCode extends Model
{
    protected $table = 'rate_codes';

    protected $fillable = [
        'name', 'type', 'rate', 'fixed'
    ];
}
