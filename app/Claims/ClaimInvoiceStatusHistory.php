<?php

namespace App\Claims;

use App\AuditableModel;

class ClaimInvoiceStatusHistory extends AuditableModel
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'claim_invoice_status_history';
}
