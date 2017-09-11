<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentQueue extends Model
{
    protected $table = 'payment_queue';

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function method()
    {
        return $this->morphTo();
    }
}
