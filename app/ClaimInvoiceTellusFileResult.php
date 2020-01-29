<?php

namespace App;

use App\Claims\Contracts\TransmissionFileResultInterface;
use Carbon\Carbon;

/**
 * App\ClaimInvoiceTellusFileResult
 *
 * @property int $id
 * @property int $tellus_file_id
 * @property string|null $service_date
 * @property string|null $reference_id
 * @property string|null $service_code
 * @property string $status_code
 * @property string $import_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read \App\ClaimInvoiceTellusFile $tellusFile
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClaimInvoiceTellusFileResult newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClaimInvoiceTellusFileResult newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClaimInvoiceTellusFileResult query()
 * @mixin \Eloquent
 */
class ClaimInvoiceTellusFileResult extends AuditableModel implements TransmissionFileResultInterface
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
     * Get the TellusFile Relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tellusFile()
    {
        return $this->belongsTo(ClaimInvoiceTellusFile::class, 'tellus_file_id', 'id');
    }
    // **********************************************************
    // ACCESSORS
    // **********************************************************

    /**
     * @inheritDoc
     */
    public function getServiceCode(): string
    {
        return $this->service_code;
    }

    /**
     * @inheritDoc
     */
    public function getServiceDate(): Carbon
    {
        return Carbon::parse($this->service_date);
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode(): string
    {
        return $this->status_code;
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        return $this->import_status;
    }
}
