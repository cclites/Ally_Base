<?php
namespace App\Traits;

use App\BusinessChain;
use App\Contracts\BelongsToChainsInterface;
use App\User;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToChains
{
    /**
     * Whether the entity belongs to the provided business chain model or id.
     *
     * @param \App\BusinessChain|int $businessChain
     * @return bool
     */
    public function belongsToChain($businessChain)
    {
        if ($businessChain instanceof BusinessChain) {
            $businessChain = $businessChain->id;
        }

        return in_array($businessChain, $this->getChainIds());
    }

    /**
     * Returns the provided business IDs that are actually attached to the given entity
     *
     * @param array $chainIds
     * @return array
     */
    public function filterAttachedChains(array $chainIds)
    {
        return array_intersect($chainIds, $this->getChainIds());
    }


    /**
     * Whether the entity shares any of the same chains as the provided entity
     *
     * @param \App\Contracts\BelongsToChainsInterface $entity
     * @return bool
     */
    public function sharesChainWith(BelongsToChainsInterface $entity)
    {
        return count($this->filterAttachedChains($entity->getChainIds())) > 0;
    }

    /**
     * A query scope for filtering results by a chain that is authorized to be queried by $authorizedUser
     * NOTE: This should be used in office user controllers
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \App\User|null $authorizedUser
     * @return void
     */
    public function scopeForAuthorizedChain(Builder $builder, User $authorizedUser = null)
    {
        if ($authorizedUser === null) $authorizedUser = auth()->user();

        if ($authorizedUser->role_type === 'admin') return;
        if (!$authorizedUser->officeUser) throw new \Exception('This user type does not support forAuthorizedChain');

        $builder->forChains([$authorizedUser->officeUser->chain_id]);
    }
}