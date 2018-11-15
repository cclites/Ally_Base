<?php
namespace App\Contracts;

use App\User;
use Illuminate\Database\Eloquent\Builder;

interface BelongsToBusinessesInterface
{
    /**
     * Return an array of business IDs the entity is attached to
     *
     * @return array
     */
    public function getBusinessIds();

    /**
     * Whether the entity belongs to the provided business model or id.
     *
     * @param \App\Business|int $business
     * @return bool
     */
    public function belongsToBusiness($business);

    /**
     * Whether the entity belongs to any of the provided business models or ids.
     *
     * @param \App\Business[]|int[] $businesses
     * @return bool
     */
    public function belongsToAnyBusiness(array $businesses);

    /**
     * Returns the provided business IDs that are actually attached to the given entity
     *
     * @param \App\Contracts\BelongsToBusinessesInterface $entity
     * @param array $businessIds
     * @return array
     */
    public function filterAttachedBusinesses(BelongsToBusinessesInterface $entity, array $businessIds);

    /**
     * Whether the entity shares any of the same businesses as the provided entity
     *
     * @param \App\Contracts\BelongsToBusinessesInterface $entity
     * @return bool
     */
    public function sharesBusinessWith(BelongsToBusinessesInterface $entity);

    /**
     * A query scope for filtering results by related business IDs
     * Note: Use forAuthorizedBusinesses in controllers
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $businessIds
     * @return void
     */
    public function scopeForBusinesses(Builder $builder, array $businessIds);

    /**
     * A query scope for filtering results by related business IDs that are authorized to be queried by $authorizedUser
     * NOTE: This should be used in controllers
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array|null $businessIds
     * @param \App\User|null $authorizedUser
     * @return void
     */
    public function scopeForRequestedBusinesses(Builder $builder, array $businessIds = null, User $authorizedUser = null);
}