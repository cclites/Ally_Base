<?php

namespace App;

use App\Billing\ClientInvoice;
use Illuminate\Database\Eloquent\Model;

class QuickbooksClientInvoice extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the ClientInvoice relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function clientInvoice()
    {
        return $this->hasMany(ClientInvoice::class, 'id', 'client_invoice_id');
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
