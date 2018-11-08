<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientMeta extends Model
{
    protected $table = 'client_meta';
    protected $fillable = ['key', 'value'];
}
