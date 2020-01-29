<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ScrubsForSeeding;

/**
 * App\ChainClientTypeSettings
 *
 * @property int $id
 * @property int $business_chain_id
 * @property string|null $medicaid_1099_default
 * @property int $medicaid_1099_edit
 * @property string|null $medicaid_1099_from
 * @property string|null $private_pay_1099_default
 * @property int $private_pay_1099_edit
 * @property string|null $private_pay_1099_from
 * @property string|null $other_1099_default
 * @property int $other_1099_edit
 * @property string|null $other_1099_from
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\BusinessChain $chain
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChainClientTypeSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChainClientTypeSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ChainClientTypeSettings query()
 * @mixin \Eloquent
 */
class ChainClientTypeSettings extends Model
{
    protected $table = 'chain_client_type_settings';

    protected $fillable = [
        'business_chain_id',
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
