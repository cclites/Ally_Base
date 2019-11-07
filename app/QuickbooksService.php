<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;

class QuickbooksService extends AuditableModel
{
    use SoftDeletes;

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

    /**
     * @var string  The column to sort by default when using ordered()
     */
    protected $orderedColumn = 'name';

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the Business relation.
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

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

}
