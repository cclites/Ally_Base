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

    /**
     * Get the payment method description for the ClaimInvoice display.
     *
     * @return string
     */
    public function getPaymentMethod() : string
    {
        $str = '';
        if ($this->description) {
            $str = ucwords(str_replace('_', ' ', $this->description)) . ' ';
        }

        if ($this->type && $this->reference) {
            $str .= "($this->type: {$this->reference})";
        } else {
            if ($this->type) {
                $str .= "($this->type)";
            } else if ($this->reference) {
                $str .= "($this->reference)";
            }
        }

        return $str;
    }
}