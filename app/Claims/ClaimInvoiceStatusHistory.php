<?php

namespace App\Claims;

use App\AuditableModel;

/**
 * App\Claims\ClaimInvoiceStatusHistory
 *
 * @property int $id
 * @property int $claim_invoice_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoiceStatusHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoiceStatusHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoiceStatusHistory query()
 * @mixin \Eloquent
 */
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
