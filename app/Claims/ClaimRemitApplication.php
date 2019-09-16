<?php

namespace App\Claims;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\AuditableModel;

/**
 * App\Claims\ClaimRemitApplication
 *
 * @property int $id
 * @property int $claim_remit_id
 * @property int|null $claim_invoice_id
 * @property int|null $claim_invoice_item_id
 * @property string $application_type
 * @property float $amount_applied
 * @property int $is_interest
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Claims\ClaimInvoice|null $claimInvoice
 * @property-read \App\Claims\ClaimInvoiceItem|null $claimInvoiceItem
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimRemitApplication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimRemitApplication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimRemitApplication query()
 * @mixin \Eloquent
 */
class ClaimRemitApplication extends AuditableModel
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

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
    protected $appends = [];

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
     * Get the ClaimInvoice relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function claimInvoice()
    {
        return $this->belongsTo(ClaimInvoice::class);
    }

    /**
     * Get the ClaimInvoiceItem relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function claimInvoiceItem()
    {
        return $this->belongsTo(ClaimInvoiceItem::class);
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
