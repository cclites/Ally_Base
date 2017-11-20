<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $guarded = ['id'];

    public function business()
    {
        return $this->belongsTo('App\Business');
    }

    public function caregiver()
    {
        return $this->belongsTo('App\Caregiver');
    }

    public function client()
    {
        return $this->belongsTo('App\Client');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'created_by');
    }
}
