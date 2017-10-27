<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShiftActivity extends Model
{
    protected $table = 'shift_activities';
    public $timestamps = false;
    protected $guarded = ['id'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

}
