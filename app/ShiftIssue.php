<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShiftIssue extends Model
{
    protected $table = 'shift_issues';
    public $timestamps = false;
    protected $guarded = ['id'];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
