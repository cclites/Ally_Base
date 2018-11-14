<?php
namespace App\Traits;

use App\Business;
use App\Contracts\BelongsToBusinessesInterface;
use App\User;
use Auth;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait BelongsToBusinesses
 * @package App\Traits
 */
trait BelongsToBusinesses
{
    /**
     * Whether the entity belongs to the provided business model or id.
     *
     * @param \App\Business|int $business
     * @return bool
     */
    public function belongsToBusiness($business)
    {
        if ($business instanceof Business) {
            $business = $business->id;
        }

        return in_array($business, $this->getBusinessIds());
    }

    /**
     * Whether the entity belongs to any of the provided business models or ids.
     *
     * @param \App\Business[]|int[] $businesses
     * @return bool
     */
    public function belongsToAnyBusiness(array $businesses)
    {
        foreach($businesses as $business) {
            if ($this->belongsToBusiness($business)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the provided business IDs that are actually attached to the given entity
     *
     * @param \App\Contracts\BelongsToBusinessesInterface $entity
     * @param array $businessIds
     * @return array
     */
    public function filterAttachedBusinesses(BelongsToBusinessesInterface $entity, array $businessIds)
    {
        return array_intersect($businessIds, $entity->getBusinessIds());
    }

    /**
     * Whether the entity shares any of the same businesses as the given entity
     *
     * @param \App\Contracts\BelongsToBusinessesInterface $entity
     * @return bool
     */
    public function sharesBusinessWith(BelongsToBusinessesInterface $entity)
    {
        return count($this->filterAttachedBusinesses($entity, $this->getBusinessIds())) > 0;
    }

    /**
     * A query scope for filtering results by related business IDs that are authorized to be queried by $authorizedUser
     * NOTE: This should be used in controllers
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $businessIds
     * @param \App\User|null $authorizedUser
     * @return void
     */
    public function scopeForAuthorizedBusinesses(Builder $builder, array $businessIds, User $authorizedUser = null)
    {
        if (!$authorizedUser) $authorizedUser = Auth::user();

        if ($authorizedUser->role_type !== 'admin') {
            $businessIds = $this->filterAttachedBusinesses($authorizedUser, $businessIds);
            // If empty, filter by all businesses the authorized user has access to
            if (!count($businessIds)) $businessIds = $authorizedUser->getBusinessIds();
        }

        $builder->forBusinesses($businessIds);
    }
}