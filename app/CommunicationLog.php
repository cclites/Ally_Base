<?php

namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;
use Illuminate\Database\Eloquent\Model;

class CommunicationLog extends Model implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

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
     * A CommunicationLog belongs to a User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the Business relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function business()
    {
        return $this->belongsTo(\App\Business::class);
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
