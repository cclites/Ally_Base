<?php

namespace App\Billing;

use App\AuditableModel;
use App\Billing\Contracts\PaymentInterface;
use Carbon\Carbon;

/**
 * Class OfflineInvoicePayment
 * @package App\Billing
 */
class OfflineInvoicePayment extends AuditableModel implements PaymentInterface
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the ClientInvoice relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function invoice()
    {
        return $this->belongsTo(ClientInvoice::class, 'client_invoice_id', 'id');
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

    /**
     * @inheritDoc
     */
    public function getDate(): Carbon
    {
        return Carbon::parse($this->payment_date);
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        if (filled($this->description) && filled($this->type) && filled($this->reference)) {
            return snake_to_title_case($this->description) . ' (' . $this->type . ': ' . $this->reference . ')';
        }

        if (empty($this->description) && empty($this->type) && empty($this->reference)) {
            return 'Payment';
        }

        $typeAndReference = '(' . $this->type . ': ' . $this->reference . ')';
        if (empty($this->type)) {
            $typeAndReference = '(' . $this->reference . ')';
        } else if (empty($this->reference)) {
            $typeAndReference = '(' . $this->type . ')';
        }

        if (filled($this->description)) {
            return snake_to_title_case($this->description) . ' ' . ($typeAndReference == '()' ? '' : $typeAndReference);
        }

        return $typeAndReference;
    }

    /**
     * @inheritDoc
     */
    public function getAmount(): float
    {
        return (float) $this->amount;
    }

    /**
     * @inheritDoc
     */
    public function getAmountAppliedTowardsInvoice(ClientInvoice $invoice) : float
    {
        // Offline AR only applies payments towards a single invoice at a time.
        return (float) $this->amount;
    }

    /**
     * @inheritDoc
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }
}
