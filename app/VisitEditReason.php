<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VisitEditReason extends Model
{
    protected $guarded = ['id'];




    public function getFormattedNameAttribute()
    {
        return $this->code . ": " . $this->description;
    }
}
