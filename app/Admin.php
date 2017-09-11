<?php

namespace App;

use App\Traits\IsUserRole;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use IsUserRole;

    protected $table = 'admins';
    public $timestamps = false;
    public $fillable = [];

}
