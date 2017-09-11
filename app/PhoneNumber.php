<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhoneNumber extends Model
{
    protected $table = 'phone_numbers';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
