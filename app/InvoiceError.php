<?php

namespace App;

use App\Billing\ClientInvoice;
use Illuminate\Database\Eloquent\Model;

class InvoiceError extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice_errors';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    public $guarded = [ 'id' ];

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
    public $appends = [];


    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    public function invoice()
    {
        return $this->belongsTo( ClientInvoice::class );
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
