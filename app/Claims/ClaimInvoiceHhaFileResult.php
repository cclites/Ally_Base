<?php

namespace App\Claims;

use App\BaseModel;
use App\Claims\Contracts\TransmissionFileResultInterface;
use Carbon\Carbon;

/**
 * App\Claims\ClaimInvoiceHhaFileResult
 *
 * @property int $id
 * @property int $hha_file_id
 * @property string|null $service_date
 * @property string|null $reference_id
 * @property string|null $service_code
 * @property string $status_code
 * @property string $import_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Claims\ClaimInvoiceHhaFile $hhaFile
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoiceHhaFileResult newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoiceHhaFileResult newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimInvoiceHhaFileResult query()
 * @mixin \Eloquent
 */
class ClaimInvoiceHhaFileResult extends BaseModel implements TransmissionFileResultInterface
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
     * Get the HhaFile Relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hhaFile()
    {
        return $this->belongsTo(ClaimInvoiceHhaFile::class, 'hha_file_id', 'id');
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
