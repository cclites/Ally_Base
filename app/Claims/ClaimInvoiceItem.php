<?php

namespace App\Claims;

use App\Claims\Exceptions\ClaimBalanceException;
use App\AuditableModel;

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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Claims\ClaimAdjustment[] $adjustments
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
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['date'];

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
     * Get the ClaimAdjustments relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function adjustments()
    {
        return $this->hasMany(ClaimAdjustment::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    /**
     * Get the a formatted claimable type string.
     *
     * @return string
     */
    public function getTypeAttribute(): string
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
     * Get the total amount paid/adjusted.
     *
     * @return float
     */
    public function getAmountPaid(): float
    {
        return subtract(floatval($this->amount), floatval($this->amount_due));
    }

    /**
     * Calculate the amount due for this ClaimInvoiceItem
     * from all the remit amounts applied to it.
     *
     * @return void
     * @throws ClaimBalanceException
     */
    public function updateBalance(): void
    {
        $this->refresh();

        $totalApplied = $this->adjustments->reduce(function ($carry, $application) {
            return add($carry, floatval($application->amount_applied));
        }, floatval(0));

        $amountDue = subtract(floatval($this->amount), $totalApplied);

        if ($amountDue < floatval(0)) {
            throw new ClaimBalanceException('Claim invoice items cannot have a negative balance.');
        } elseif ($amountDue > floatval($this->amount)) {
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
    public function getServiceTimes(string $timezone = null): array
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
            $this->claimable->visit_start_time->setTimezone($timezone),
            $this->claimable->visit_end_time->setTimezone($timezone),
        ];
    }

    /**
     * Get a string summary of the claimable item.
     *
     * @param string|null $timezone
     * @return string
     */
    public function getItemSummary(string $timezone = null): string
    {
        if (empty($timezone)) {
            $timezone = $this->claim->business->getTimezone();
        }

        // Service CODE - Caregiver - 1/23/19 1:00 PM - 3:00 PM
        list($start, $end) = $this->getServiceTimes($timezone);

        $time = '';
        if (filled($start) && filled($end)) {
            $time = ' ' . $start->format('g:i A') . ' - ' . $end->format('g:i A');
        }

        $service = $this->claimable->getName();
        $caregiver = $this->claimable->getCaregiverName();
        $date = $this->date->setTimezone($timezone)->format('m/d/y');

        return "$service - $caregiver - $date{$time}";
    }
}
