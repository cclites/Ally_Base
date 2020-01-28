<?php

namespace App;

use App\Claims\ClaimInvoice;
use App\Claims\Contracts\TransmissionFileInterface;
use Carbon\Carbon;

/**
 * App\ClaimInvoiceTellusFile
 *
 * @property int $id
 * @property int $claim_invoice_id
 * @property string $filename
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read \App\Claims\ClaimInvoice $claimInvoice
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ClaimInvoiceTellusFileResult[] $results
 * @property-read int|null $results_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClaimInvoiceTellusFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClaimInvoiceTellusFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClaimInvoiceTellusFile query()
 * @mixin \Eloquent
 */
class ClaimInvoiceTellusFile extends AuditableModel implements TransmissionFileInterface
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the Claim relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function claimInvoice()
    {
        return $this->belongsTo(ClaimInvoice::class);
    }

    /**
     * Get the results relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function results()
    {
        return $this->hasMany(ClaimInvoiceTellusFileResult::class, 'tellus_file_id', 'id');
    }

    // **********************************************************
    // ACCESSORS
    // **********************************************************

    /**
     * @inheritDoc
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @inheritDoc
     */
    public function getResults(): ?iterable
    {
        return $this->results;
    }

    /**
     * @inheritDoc
     */
    public function getDate(): Carbon
    {
        return $this->created_at;
    }
}
