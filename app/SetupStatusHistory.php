<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SetupStatusHistory extends Model
{
    protected $table = 'user_setup_status_history';
    protected $guarded = ['id'];
}
