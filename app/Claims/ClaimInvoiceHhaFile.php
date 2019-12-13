<?php

namespace App\Claims;

use App\BaseModel;
use App\Claims\Contracts\TransmissionFileInterface;
use Carbon\Carbon;

class ClaimInvoiceHhaFile extends BaseModel implements TransmissionFileInterface
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
        return $this->hasMany(ClaimInvoiceHhaFileResult::class, 'hha_file_id', 'id');
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