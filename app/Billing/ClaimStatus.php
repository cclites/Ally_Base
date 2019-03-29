<?php

namespace App\Billing;

use App\AuditableModel;

class ClaimStatus extends AuditableModel
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    protected $table = 'claim_status_history';
}
