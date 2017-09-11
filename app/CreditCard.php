<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CreditCard extends Model
{
    protected $table = 'credit_cards';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
