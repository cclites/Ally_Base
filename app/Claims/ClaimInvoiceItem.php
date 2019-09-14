<?php

namespace App\Claims;

use App\AuditableModel;
use App\Claims\Exceptions\ClaimBalanceException;
use Carbon\Carbon;

/**
 * App\Claims\ClaimInvoiceItem
 *
 * @property int $id
 * @property int $claim_invoice_id
 * @property int|null $invoiceable_id
 * @property string|null $invoiceable_type
 * @property int $claimable_id
 * @property string $claimable_type
 * @property float $rate
 * @property float $units
 * @property float $amount
 * @property float $amount_due
 * @property string $date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Claims\ClaimInvoice $claim
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $claimable
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Claims\ClaimRemitApplication[] $remitApplications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoiceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoiceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoiceItem query()
 * @mixin \Eloquent
 */
class ClaimInvoiceItem extends AuditableModel
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
    public $with = ['claimable'];

    /**
     * The relationships that should be marked as updated_at when
     * this resource is updated.
     *
     * @var array
     */
    protected $touches = ['claim'];

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
     * Get the parent ClaimInvoice relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function claim()
    {
        return $this->belongsTo(ClaimInvoice::class, 'claim_invoice_id');
    }

    /**
     * Get the claimable object.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function claimable()
    {
        return $this->morphTo('claimable', 'claimable_type', 'claimable_id');
    }

    /**
     * Get the ClaimRemitApplications relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function remitApplications()
    {
        return $this->hasMany(ClaimRemitApplication::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    /**
     * Get the a formatted claimable type string.
     *
     * @return string
     */
    public function getTypeAttribute() : string
    {
        switch ($this->claimable_type) {
            case ClaimableService::class:
                return 'Service';
            case ClaimableExpense::class:
                return 'Expense';
            default:
                return 'ERROR';
        }
    }

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Calculate the amount due for this ClaimInvoiceItem
     * from all the remit amounts applied to it.
     *
     * @return void
     * @throws ClaimBalanceException
     */
    public function updateBalance() : void
    {
        $totalApplied = $this->remitApplications->reduce(function ($carry, $application) {
            return add($carry, floatval($application->amount_applied));
        }, floatval(0));

        $amountDue = subtract(floatval($this->amount), $totalApplied);

        if ($amountDue < floatval(0)) {
            throw new ClaimBalanceException('Claim invoice items cannot have a negative balance.');
        } else if ($amountDue > floatval($this->amount)) {
            throw new ClaimBalanceException('Claim invoice items cannot have a balance greater than their total amount.');
        }

        $this->update(['amount_due' => $amountDue]);
    }

    /**
     * Get the start and end times of the claimable service.
     *
     * @param string $timezone
     * @return array
     */
    public function getServiceTimes(string $timezone = null) : array
    {
        if ($this->claimable_type != ClaimableService::class) {
            return [null, null];
        }

        if (empty($this->claimable->visit_start_time) || empty($this->claimable->visit_end_time)) {
            return [null, null];
        }

        if (empty($timezone)) {
            $timezone = $this->claim->business->getTimezone();
        }

        return [
            $this->claimable->visit_start_time->setTimezone($timezone)->toDateTimeString(),
            $this->claimable->visit_end_time->setTimezone($timezone)->toDateTimeString(),
        ];
    }
}