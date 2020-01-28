<?php

namespace App;

use App\Contracts\BelongsToChainsInterface;
use App\Traits\BelongsToOneChain;

/**
 * App\ExpirationType
 *
 * @property int $id
 * @property string $type
 * @property int|null $chain_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read \App\BusinessChain|null $businessChain
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ExpirationType forAuthorizedChain(\App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ExpirationType forChains($chains)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ExpirationType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ExpirationType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ExpirationType query()
 * @mixin \Eloquent
 */
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