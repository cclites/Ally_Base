<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientPreferences extends Model
{
    protected $table = 'client_preferences';
    protected $guarded = ['id'];
    public $incrementing = false;
    public $timestamps = false;
}
