<?php

namespace App;

use App\Contracts\BelongsToChainsInterface;
use App\Traits\BelongsToOneChain;

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
