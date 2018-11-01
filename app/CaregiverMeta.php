<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CaregiverMeta extends Model
{
    protected $table = 'caregiver_meta';
    protected $fillable = ['key', 'value'];
}
