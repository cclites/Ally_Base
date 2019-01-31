<?php

namespace App;

use App\Traits\BelongsToOneBusiness;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\BelongsToBusinessesInterface;

class SalesPerson extends Model implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    protected $guarded = ['id'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
