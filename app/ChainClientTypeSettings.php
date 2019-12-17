<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ScrubsForSeeding;

class ChainClientTypeSettings extends Model
{
    protected $table = 'chain_client_type_settings';

    protected $fillable = [
        'medicaid_1099_default',
        'medicaid_1099_edit',
        'medicaid_1099_from',
        'private_pay_1099_default',
        'private_pay_1099_edit',
        'private_pay_1099_from',
        'other_1099_default',
        'other_1099_edit',
        'other_1099_from',
    ];

    public function chain(){
        return $this->belongsTo(BusinessChain::class, 'business_chain_id');
    }
}
