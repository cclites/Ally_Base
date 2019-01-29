<?php

namespace App;

use App\Traits\BelongsToOneBusiness;
use Illuminate\Database\Eloquent\Model;

class SalesPerson extends Model
{
    use BelongsToOneBusiness;

    protected $guarded = ['id'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
