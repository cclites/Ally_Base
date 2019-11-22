<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Caregiver1099 extends Model
{

    protected $table = 'caregiver_1099s';
    protected $guarded = ['id'];

    // Relations
    public function caregiver(){
        return (Caregiver::class);
    }

}
