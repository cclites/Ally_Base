<?php

namespace App;

use App\Contracts\BelongsToChainsInterface;
use App\Traits\BelongsToOneChain;

/**
 * App\DeactivationReason
 *
 * @property int $id
 * @property string $name
 * @property int|null $chain_id
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read \App\BusinessChain|null $businessChain
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeactivationReason forAuthorizedChain(\App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeactivationReason forChains($chains)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeactivationReason newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeactivationReason newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeactivationReason query()
 * @mixin \Eloquent
 */
class DeactivationReason extends AuditableModel implements BelongsToChainsInterface
{
    use BelongsToOneChain;

    protected $guarded = ['id'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    public $with = [];
    
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * The column to sort by default when using ordered().
     *
     * @var string
     */
    protected $orderedColumn = 'name';

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
}
