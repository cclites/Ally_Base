<?php

namespace App\Claims;

use App\AuditableModel;
use App\Billing\Payer;
use App\Business;
use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;

class ClaimRemit extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * @var string  The column to sort by default when using ordered()
     */
    protected $orderedColumn = 'id';

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        //
        parent::boot();
    }

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the Payer relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    /**
     * Get the Business relationship.
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
