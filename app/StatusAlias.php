<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StatusAlias extends Model
{
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

    /**
     * Get the owning business.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

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
