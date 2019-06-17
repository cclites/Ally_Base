<?php

namespace App\Billing;

use App\AuditableModel;
use App\Billing\Claims\HhaClaimTransmitter;
use App\Billing\Claims\ManualClaimTransmitter;
use App\Billing\Claims\TellusClaimTransmitter;
use App\Billing\Contracts\ClaimTransmitterInterface;
use App\Billing\Exceptions\ClaimTransmissionException;

/**
 * \App\Billing\Claim
 *
 * @property int $id
 * @property int $client_invoice_id
 * @property float $amount
 * @property float $amount_paid
 * @property string $status
 * @property string|null $service
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Billing\ClientInvoice $invoice
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\ClaimPayment[] $payments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\ClaimStatusHistory[] $statuses
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @mixin \Eloquent
 */
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

    function getAmount(): float
    {
        return (float) $this->amount;
    }

    function getAmountPaid(): float
    {
        return (float) $this->amount_paid;
    }

    function getAmountDue(): float
    {
        return (float) bcsub($this->getAmount(), $this->getAmountPaid(), 2);
    }

    function addPayment(ClaimPayment $payment): bool
    {
        if ($payment->claim_id) {
            throw new \InvalidArgumentException('Cannot add an old claim payment.');
        }

        if ($this->payments()->save($payment)) {
            return (bool) $this->increment('amount_paid', $payment->amount);
        }

        return false;
    }

    function removePayment(ClaimPayment $payment): bool
    {
        if ($payment = $this->payments->where('id', $payment->id)->first()) {
            $payment->delete();
            return (bool) $this->decrement('amount_paid', $payment->amount);
        }

        return false;
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
                'amount' => $invoice->getAmount(),
                'status' => ClaimStatus::CREATED(),
            ]);

            $claim->statuses()->create(['status' => ClaimStatus::CREATED()]);
        }

        return $claim;
    }

    /**
     * Get the ClaimTransmitter for the given service.
     *
     * @param ClaimService $service
     * @return ClaimTransmitterInterface
     * @throws ClaimTransmissionException
     */
    public static function getTransmitter(ClaimService $service) : ClaimTransmitterInterface
    {
        switch ($service) {
            case ClaimService::HHA():
                return new HhaClaimTransmitter();
                break;
            case ClaimService::TELLUS():
                return new TellusClaimTransmitter();
                break;
            case ClaimService::CLEARINGHOUSE():
                throw new ClaimTransmissionException('Claim service not supported.');
                break;
            case ClaimService::FAX():
            case ClaimService::EMAIL():
                return new ManualClaimTransmitter();
                break;
            default:
                throw new ClaimTransmissionException('Claim service not supported.');
        }
    }
}
