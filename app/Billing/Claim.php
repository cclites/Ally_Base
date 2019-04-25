<?php

namespace App\Billing;

use App\AuditableModel;
use App\ClaimPayment;
use Carbon\Carbon;
use App\Shift;

class Claim extends AuditableModel
{
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
    public $with = ['statuses'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

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

    /**
     * Get the status relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function statuses()
    {
        return $this->hasMany(ClaimStatusHistory::class);
    }

    /**
     * Get the claim payments relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function payments()
    {
        return $this->hasMany(ClaimPayment::class);
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
     * Recalculate the balance of the claim and update the stored value.
     *
     * @return Claim
     */
    public function recalculateBalance() : self
    {
        $payments = $this->payments->sum('amount');

        $this->balance = floatval($this->amount) - floatval($payments);

        $this->save();

        return $this;
    }

    /**
     * Set the status of the claim, and add to it's status history.
     *
     * @param \App\Billing\ClaimStatus $status
     * @param array $otherUpdates
     */
    public function updateStatus(ClaimStatus $status, array $otherUpdates = []) : void
    {
        $this->update(array_merge(['status' => $status], $otherUpdates));
        $this->statuses()->create(['status' => $status]);
    }

    /**
     * Get the claim from a ClientInvoice or create a
     * new claim from the invoice data.
     *
     * @param \App\Billing\ClientInvoice $invoice
     * @return \App\Billing\Claim
     */
    public static function getOrCreate(ClientInvoice $invoice) : Claim
    {
        $claim = $invoice->claim;

        if (empty($claim)) {
            $claim = Claim::create([
                'client_invoice_id' => $invoice->id,
                'amount' => $invoice->amount,
                'balance' => $invoice->amount,
                'status' => ClaimStatus::CREATED(),
            ]);

            $claim->statuses()->create(['status' => ClaimStatus::CREATED()]);
        }

        return $claim;
    }
}
