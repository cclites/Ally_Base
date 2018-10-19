<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferralSource extends Model
{
    protected $fillable = [
        'business_id',
        'organization',
        'contact_name',
        'phone'
    ];

    public function client() {
        return $this->hasMany('App\Client');
    }

    public function prospect() {
        return $this->hasMany('App\Prospect');
    }
}
