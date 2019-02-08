<?php
namespace App\Billing\Invoiceable\Traits;

use App\Traits\BelongsToBusinesses;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToThroughShift
{
    use BelongsToBusinesses;

    /**
     * Return an array of business IDs the entity is attached to
     *
     * @return array
     */
    public function getBusinessIds()
    {
        return [$this->shift->business_id];
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
        $builder->whereHas('shift', function($q) use ($businessIds) {
            $q->whereIn('business_id', $businessIds);
        });
    }

    /**
     * A query scope for filtering invoicables by related caregiver IDs
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $caregiverIds
     * @return void
     */
    public function scopeForCaregivers(Builder $builder, array $caregiverIds)
    {
        $builder->whereHas('shift', function($q) use ($caregiverIds) {
            $q->whereIn('caregiver_id', $caregiverIds);
        });
    }
}