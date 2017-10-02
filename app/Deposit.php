<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $table = 'deposits';
    protected $guarded = ['id'];

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function method()
    {
        return $this->morphTo();
    }

    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'deposit_payments');
    }
}
