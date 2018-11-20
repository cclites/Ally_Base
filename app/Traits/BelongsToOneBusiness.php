<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToOneBusiness
{
    use BelongsToBusinesses;

    /**
     * Return an array of business IDs the entity is attached to
     *
     * @return array
     */
    public function getBusinessIds()
    {
        return [$this->business_id];
    }

    /**
     * A query scope for filtering results by related business IDs
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $businessIds
     * @return void
     */
    public function scopeForBusinesses(Builder $builder, array $businessIds)
    {
        $builder->whereIn('business_id', $businessIds);
    }
}