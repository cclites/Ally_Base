<?php

namespace App;

use App\Billing\ClientInvoice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class QuickbooksClientInvoice extends Model
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
    public function clientInvoice()
    {
        return $this->belongsTo(ClientInvoice::class);
    }

    /**
     * Get the status relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statuses()
    {
        return $this->hasMany(QuickbooksClientInvoiceStatusHistory::class, 'quickbooks_client_invoice_id', 'id')
            ->latest();
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    /**
     * Filter by Business.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param int $businessId
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    /**
     * Filter by status.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param QuickbooksInvoiceStatus $status
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWithStatus($query, QuickbooksInvoiceStatus $status)
    {
        return $query->where('status', $status);
    }

    // **********************************************************
    // ACCESSORS
    // **********************************************************

    /**
     * Get the timestamp of the last status update.
     *
     * @return Carbon
     */
    public function getLastStatusUpdate() : Carbon
    {
        if ($this->statuses->isEmpty()) {
            return $this->created_at;
        }

        return $this->statuses->first()->created_at;
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Set the status of the invoice, and add to it's status history.
     *
     * @param \App\QuickbooksInvoiceStatus $status
     * @param array $otherUpdates
     */
    public function updateStatus(QuickbooksInvoiceStatus $status, array $otherUpdates = []): void
    {
        $this->update(array_merge(['status' => $status], $otherUpdates));
        $this->statuses()->create(['status' => $status]);
    }
}
