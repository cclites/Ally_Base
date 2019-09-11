<?php

namespace App\Claims;

use App\Claims\Exceptions\ClaimBalanceException;
use App\Contracts\BelongsToBusinessesInterface;
use App\Billing\Contracts\InvoiceInterface;
use App\Traits\BelongsToOneBusiness;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Billing\ClaimStatus;
use App\Billing\ClientInvoice;
use App\Billing\ClaimPayment;
use App\Billing\ClientPayer;
use App\AuditableModel;
use App\Billing\Payer;
use App\Business;
use App\Client;

/**
 * App\Claims\ClaimInvoice
 *
 * @property int $id
 * @property int $business_id
 * @property int $client_invoice_id
 * @property string $name
 * @property float $amount
 * @property float $amount_due
 * @property string $status
 * @property string|null $transmission_method
 * @property int $client_id
 * @property string $client_first_name
 * @property string $client_last_name
 * @property string|null $client_dob
 * @property string|null $client_medicaid_id
 * @property string|null $client_medicaid_diagnosis_codes
 * @property int $payer_id
 * @property string $payer_name
 * @property string|null $payer_code
 * @property string|null $plan_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business $business
 * @property-read \App\Client $client
 * @property-read \App\Billing\ClientInvoice $clientInvoice
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Claims\ClaimInvoiceItem[] $items
 * @property-read \App\Billing\Payer $payer
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\ClaimPayment[] $payments
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoice forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoice forClient($clientId = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoice forDateRange($start, $end)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoice forPayer($payerId = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoice forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoice hasBalance()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoice query()
 * @mixin \Eloquent
 */
class ClaimInvoice extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

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
     * Get the Business relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the ClientInvoice relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clientInvoice()
    {
        return $this->belongsTo(ClientInvoice::class);
    }

    /**
     * Get the ClaimInvoiceItems relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(ClaimInvoiceItem::class, 'claim_invoice_id', 'id');
    }

    function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the Payer relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    public function payments()
    {
        return $this->hasMany(ClaimPayment::class);
    }

    // **********************************************************
    // ACCESSORS
    // **********************************************************

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus() : string
    {
        return $this->status;
    }

    function getDate() : Carbon
    {
        return $this->created_at;
    }

    function getAmount() : float
    {
        return floatval($this->amount);
    }

    function getAmountDue() : float
    {
        return floatval($this->amount_due);
    }







    function getClientPayer(): ?ClientPayer
    {
        return $this->clientPayer;
    }

    function getAmountPaid(): float
    {
        return subtract(floatval($this->amount), floatval($this->amount_due));
    }

    /**
     * @return \Illuminate\Support\Collection|\App\Billing\ClaimInvoiceItem[]
     */
    function getItems(): Collection
    {
        return $this->items;
    }

    /**
     *
     * because there is no 'group' column, and this information is more-or-less computed by the editable claim data,
     * I am going to do some manual joining and formatting for the invoice here
     *
     * basically, group by 'shift'..
     *  - the shift row title will be the computed 'group' name..
     *  - each item within it will either be the service rendered or the expense listed
     */
    function getItemGroups(): Collection
    {
        $items = $this->getItems()->sortBy('created_at');

        $shifts = [];

        foreach ($items as $item) {

            $shifts[$item->getShiftTitle()][] = $item;
        }

        return collect($shifts);
    }

    /**
     * Get whether or not the claim invoice has been transmitted.
     *
     * @return bool
     */
    public function hasBeenTransmitted(): bool
    {
        return !in_array($this->status, ClaimStatus::notTransmittedStatuses());
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    /**
     * Filter by payer_id (optional).
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param null|int|string $payerId
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForPayer($query, $payerId = null)
    {
        if (empty($payerId)) {
            return $query;
        }

        return $query->where('payer_id', $payerId);
    }

    /**
     * Filter by client_id (optional).
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param null|int|string $clientId
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForClient($query, $clientId = null)
    {
        if (empty($clientId)) {
            return $query;
        }

        return $query->where('client_id', $clientId);
    }

    /**
     * Filter by date range.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param \Carbon\Carbon $start
     * @param \Carbon\Carbon $end
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForDateRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Filter to only Claims that have a balance.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeHasBalance($query)
    {
        return $query->where('amount_due', '<>', '0');
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Update the amount and amount due from the ClaimInvoiceItem values
     *
     * @throws ClaimBalanceException
     */
    public function updateBalances() : void
    {
        $items = $this->fresh()->items;

        $amount = $items->reduce(function (float $carry, ClaimInvoiceItem $item) {
            return add($carry, (float)$item->amount);
        }, (float)0.00);

        $amount_due = $items->reduce(function (float $carry, ClaimInvoiceItem $item) {
            return add($carry, (float)$item->amount_due);
        }, (float)0.00);

        if ($amount_due < floatval(0)) {
            throw new ClaimBalanceException('Claim invoices cannot have a negative balance.');
        }

        $this->update(compact('amount', 'amount_due'));
    }

    // **********************************************************
    // STATIC METHODS
    // **********************************************************

    /**
     * Get the next invoice name for a client
     *
     * @param int $businessId
     * @return string
     */
    public static function getNextName(int $businessId): string
    {
        $lastName = self::where('business_id', $businessId)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->value('name');

        $minId = 1000;
        if (!$lastName) {
            $nextId = $minId;
        } else {
            $nextId = (int)substr($lastName, strpos($lastName, '-') + 1) + 1;
        }

        if ($nextId < $minId) {
            $nextId = $minId;
        }

        return "${businessId}-${nextId}";
    }
}
