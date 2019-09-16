<?php

namespace App\Claims;

use App\Claims\Exceptions\ClaimBalanceException;
use App\Contracts\BelongsToBusinessesInterface;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToOneBusiness;
use App\AuditableModel;
use App\Billing\Payer;
use App\Business;

/**
 * App\Claims\ClaimRemit
 *
 * @property int $id
 * @property int $business_id
 * @property \Illuminate\Support\Carbon $date
 * @property string $payment_type
 * @property int|null $payer_id
 * @property string|null $reference
 * @property float $amount
 * @property float $amount_applied
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Claims\ClaimRemitApplication[] $applications
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business $business
 * @property-read \App\Billing\Payer|null $payer
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimRemit forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimRemit forDateRange($start, $end)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimRemit forPayer($payerId = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimRemit forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimRemit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimRemit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimRemit query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimRemit withReferenceId($referenceId = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimRemit withStatus(\App\Claims\ClaimRemitStatus $remitStatus = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimRemit withType($remitType = null)
 * @mixin \Eloquent
 */
class ClaimRemit extends AuditableModel implements BelongsToBusinessesInterface
{
    use SoftDeletes, BelongsToOneBusiness;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * @var string  The column to sort by default when using ordered()
     */
    protected $orderedColumn = 'id';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['date', 'created_at', 'updated_at'];

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
     * Get the Payer relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    /**
     * Get the Business relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the ClaimRemitApplication relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function applications()
    {
        return $this->hasMany(ClaimRemitApplication::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // ACCESSORS
    // **********************************************************

    /**
     * Get the amount still available to apply.
     *
     * @return float
     */
    public function getAmountAvailable() : float
    {
        return subtract((float) $this->amount, (float) $this->amount_applied);
    }

    /**
     * Get the status of the ClaimRemit.
     *
     * @return ClaimRemitStatus
     */
    public function getStatus() : ClaimRemitStatus
    {
        if ((float)$this->amount === (float)$this->amount_applied) {
            return ClaimRemitStatus::FULLY_APPLIED();
        } else if ((float)$this->amount_applied == 0) {
            return ClaimRemitStatus::NOT_APPLIED();
        }

        return ClaimRemitStatus::PARTIALLY_APPLIED();
    }

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    /**
     * Filter by date range.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param $start
     * @param $end
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForDateRange($query, $start, $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }

    /**
     * Filter by payer (optional).
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param mixed $payerId
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
     * Filter by reference no (optional).
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param null $referenceId
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWithReferenceId($query, $referenceId = null)
    {
        if (empty($referenceId)) {
            return $query;
        }

        return $query->where('reference', $referenceId);
    }

    /**
     * Filter by type (optional).
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param null $remitType
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWithType($query, $remitType = null)
    {
        if (empty($remitType)) {
            return $query;
        }

        return $query->where('payment_type', $remitType);
    }

    /**
     * Filter by status (optional).
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param null|ClaimRemitStatus $remitStatus
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWithStatus($query, ?ClaimRemitStatus $remitStatus = null)
    {
        switch ($remitStatus) {
            case ClaimRemitStatus::NOT_APPLIED():
                return $query->where('amount_applied', '=', 0);
            case ClaimRemitStatus::FULLY_APPLIED():
                return $query->whereColumn('amount', '=', 'amount_applied');
            case ClaimRemitStatus::PARTIALLY_APPLIED():
                return $query->where(function ($query) {
                    $query->whereColumn('amount', '<>', 'amount_applied')
                        ->where('amount_applied', '<>', 0);
                });
            default:
                return $query;
        }
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Calculate the balance of the ClaimRemit by adding up
     * all of it's applications.
     *
     * @return void
     * @throws ClaimBalanceException
     */
    public function updateBalance() : void
    {
        $totalApplied = $this->applications->reduce(function ($carry, $application) {
            return add($carry, floatval($application->amount_applied));
        }, floatval(0));

        if (ClaimRemitType::fromValue($this->payment_type) == ClaimRemitType::TAKE_BACK()) {
            // Take-back remits have negative amounts.

            if ($totalApplied < floatval($this->amount) || $totalApplied > floatval(0)) {
                throw new ClaimBalanceException('You cannot apply more than the total amount of the Remit.');
            }

        } else {

            if ($totalApplied > floatval($this->amount) || $totalApplied < floatval(0)) {
                throw new ClaimBalanceException('You cannot apply more than the total amount of the Remit.');
            }

        }

        $this->update(['amount_applied' => $totalApplied]);
    }
}
