<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientNarrative extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'client_narrative';

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
    public $with = ['creator'];
    
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
     * Get the creating user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

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
