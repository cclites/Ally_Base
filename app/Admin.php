<?php

namespace App;

use App\Contracts\UserRole;
use App\Traits\IsUserRole;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model implements UserRole
{
    use IsUserRole;

    protected $table = 'admins';
    public $timestamps = false;
    public $fillable = [];

}
