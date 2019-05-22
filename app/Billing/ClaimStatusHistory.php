<?php

namespace App\Billing;

use App\AuditableModel;

class ClaimStatusHistory extends AuditableModel
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
    protected $table = 'claim_status_history';
}
