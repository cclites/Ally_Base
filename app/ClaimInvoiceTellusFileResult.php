<?php

namespace App;

use App\Claims\Contracts\TransmissionFileResultInterface;
use Carbon\Carbon;

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
