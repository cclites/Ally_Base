<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientExcludedCaregiver extends Model
{
    protected $guarded = ['id'];

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
