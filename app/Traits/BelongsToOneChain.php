<?php
namespace App\Traits;

use App\BusinessChain;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToOneChain
{
    use BelongsToChains;

    /**
     * Return an array of business chain IDs the entity is attached to
     *
     * @return array
     */
    public function getChainIds()
    {
        return [$this->chain_id];
    }

    /**
     * The relationship method to access the business chain
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function businessChain()
    {
        return $this->belongsTo(BusinessChain::class, 'chain_id');
    }

    /**
     * A query scope for filtering results by related business chains
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int|\App\BusinessChain|array $chains
     * @return void
     */
    public function scopeForChains(Builder $builder, $chains)
    {
        $chains = array_map(function($chain) {
            return ($chain instanceof BusinessChain) ? $chain->id : $chain;
        }, (array) $chains);
        $builder->whereIn('chain_id', $chains);
    }
}