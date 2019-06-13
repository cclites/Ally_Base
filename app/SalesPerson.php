<?php

namespace App;

use App\Traits\BelongsToOneBusiness;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\BelongsToBusinessesInterface;
use App\Client;

use Log;

class SalesPerson extends Model implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    protected $guarded = ['id'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function clients(){
        return $this->hasMany(Client::class);
    }

    /**
     * Get only the active sales people.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWhereActive($query)
    {
        return $query->where('active', 1);
    }

    public function fullName(){
        return $this->firstname . " " . $this->lastname;
    }


}
