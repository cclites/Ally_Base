<?php
namespace App\Billing;

use App\AuditableModel;

/**
 * \App\Billing\ClaimPayment
 *
 * @property int $id
 * @property int $claim_id
 * @property string $payment_date
 * @property float $amount
 * @property string|null $type
 * @property string|null $reference
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Billing\Claim $claim
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @mixin \Eloquent
 */
class ClaimPayment extends AuditableModel
{
    protected $guarded = ['id'];

    ////////////////////////////////////
    //// Relationships
    ////////////////////////////////////

    /**
     * Get the owning claim relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }
}