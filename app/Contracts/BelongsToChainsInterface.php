<?php
namespace App\Contracts;

use App\User;
use Illuminate\Database\Eloquent\Builder;

interface BelongsToChainsInterface
{
    /**
     * Return an array of business chain IDs the entity is attached to
     *
     * @return array
     */
    public function getChainIds();

    /**
     * Whether the entity belongs to the provided business chain model or id.
     *
     * @param \App\BusinessChain|int $businessChain
     * @return bool
     */
    public function belongsToChain($businessChain);

    /**
     * Whether the entity shares any of the same chains as the provided entity
     *
     * @param \App\Contracts\BelongsToChainsInterface $entity
     * @return bool
     */
    public function sharesChainWith(BelongsToChainsInterface $entity);


    /**
     * Returns the provided business IDs that are actually attached to the given entity
     *
     * @param \App\Contracts\BelongsToChainsInterface $entity
     * @param array $chainIds
     * @return array
     */
    public function filterAttachedChains(BelongsToChainsInterface $entity, array $chainIds);

    /**
     * A query scope for filtering results by related business chains
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int|\App\BusinessChain|array $chains
     * @return void
     */
    public function scopeForChains(Builder $builder, $chains);

    /**
     * A query scope for filtering results by a chain that is authorized to be queried by $authorizedUser
     * NOTE: This should be used in office user controllers
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \App\User|null $authorizedUser
     * @return void
     */
    public function scopeForAuthorizedChain(Builder $builder,  User $authorizedUser = null);
}