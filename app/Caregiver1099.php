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
        return $this->hasOne(Caregiver::class);
    }

    public function client(){
        return $this->hasOne(Client::class);
    }

    public function business(){
        return $this->hasOne(Business::class);
    }

    //query scopes


}
