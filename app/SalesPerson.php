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
}
