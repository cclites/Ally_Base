<?php

namespace App;

use App\Contracts\BelongsToChainsInterface;
use App\Traits\BelongsToOneChain;

class ExpirationType extends AuditableModel implements BelongsToChainsInterface
{
    use BelongsToOneChain;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chain_expiration_types';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Check if type exists (case insensitive).
     *
     * @param BusinessChain $chain
     * @param string $type
     * @return bool
     */
    public static function existsForChain(BusinessChain $chain, string $type) : bool
    {
        return self::where('type', 'LIKE', "$type")
            ->where(function ($q) use ($chain) {
                $q->whereNull('chain_id')
                    ->orWhere('chain_id', $chain->id);
            })
            ->exists();
    }
}