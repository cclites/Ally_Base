<?php

namespace App;

use App\Traits\BelongsToOneChain;
use Illuminate\Database\Eloquent\Model;

/**
 * \App\StatusAlias
 *
 * @property int $id
 * @property int $chain_id
 * @property string $type
 * @property string $name
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\BusinessChain $businessChain
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StatusAlias forAuthorizedChain(\App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StatusAlias forCaregivers()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StatusAlias forChains($chains)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StatusAlias forClients()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StatusAlias newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StatusAlias newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\StatusAlias query()
 * @mixin \Eloquent
 */
class StatusAlias extends Model
{
    use BelongsToOneChain;
    
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
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
    
    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    // **********************************************************
    // MUTATORS
    // **********************************************************
    
    // **********************************************************
    // QUERY SCOPES
    // **********************************************************
    
    /**
     * Filter for only aliases of type 'caregiver'.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForCaregivers($query)
    {
        return $query->where('type', 'caregiver');
    }

    /**
     * Filter for only aliases of type 'caregiver'.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForClients($query)
    {
        return $query->where('type', 'client');
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************
}
