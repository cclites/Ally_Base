<?php

namespace App;

/**
 * App\QuickbooksClientInvoiceStatusHistory
 *
 * @property int $id
 * @property int $quickbooks_client_invoice_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickbooksClientInvoiceStatusHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickbooksClientInvoiceStatusHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickbooksClientInvoiceStatusHistory query()
 * @mixin \Eloquent
 */
class QuickbooksClientInvoiceStatusHistory extends AuditableModel
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
    protected $table = 'quickbooks_client_invoice_status_history';
}
