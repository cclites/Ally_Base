<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Caregiver;
use App\Client;
use App\Business;

class Caregiver1099 extends BaseModel
{
    protected $table = 'caregiver_1099s';
    protected $guarded = ['id'];

    // Relations
    public function caregiver(){
        return $this->belongsTo(Caregiver::class);
    }

    public function client(){
        return $this->hasOne(Client::class, 'id', 'client_id');
    }

    public function business(){
        return $this->hasOne(Business::class, 'id', 'business_id');
    }

    public function client_address3(){
        $c = $this->client;
        return $c->city . ", " . $c->state . " " . $c->zip;
    }

    public function caregiver_address3(){
        $c = $this->caregiver;
        return $c->city . ", " . $c->state . " " . $c->zip;
    }
}
