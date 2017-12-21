<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    protected $guarded = ['id'];

    /**
     * Get all of the owning signable models.
     */
    public function signable()
    {
        return $this->morphTo();
    }
    
}
