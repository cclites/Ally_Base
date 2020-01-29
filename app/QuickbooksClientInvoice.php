<?php

namespace App;

use App\Billing\ClientInvoice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\QuickbooksClientInvoice
 *
 * @property int $id
 * @property int|null $business_id
 * @property int $client_invoice_id
 * @property string|null $qb_online_id
 * @property string|null $qb_desktop_id
 * @property string|null $errors
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read \App\Billing\ClientInvoice $clientInvoice
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\QuickbooksClientInvoiceStatusHistory[] $statuses
 * @property-read int|null $statuses_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickbooksClientInvoice forBusiness($businessId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickbooksClientInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickbooksClientInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickbooksClientInvoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickbooksClientInvoice withStatus(\App\QuickbooksInvoiceStatus $status)
 * @mixin \Eloquent
 */
class QuickbooksClientInvoice extends AuditableModel
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
     * Get the ClientInvoice relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clientInvoice()
    {
        return $this->belongsTo(ClientInvoice::class);
    }

    /**
     * Get the status relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statuses()
    {
        return $this->hasMany(QuickbooksClientInvoiceStatusHistory::class, 'quickbooks_client_invoice_id', 'id')
            ->latest();
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    /**
     * Filter by Business.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param int $businessId
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    /**
     * Filter by status.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param QuickbooksInvoiceStatus $status
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWithStatus($query, QuickbooksInvoiceStatus $status)
    {
        return $query->where('status', $status);
    }

    // **********************************************************
    // ACCESSORS
    // **********************************************************

    /**
     * Get the timestamp of the last status update.
     *
     * @return Carbon
     */
    public function getLastStatusUpdate() : Carbon
    {
        if ($this->statuses->isEmpty()) {
            return $this->created_at;
        }

        return $this->statuses->first()->created_at;
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Set the status of the invoice, and add to it's status history.
     *
     * @param \App\QuickbooksInvoiceStatus $status
     * @param array $otherUpdates
     */
    public function updateStatus(QuickbooksInvoiceStatus $status, array $otherUpdates = []): void
    {
        $this->update(array_merge(['status' => $status], $otherUpdates));
        $this->statuses()->create(['status' => $status]);
    }
    
    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding;
    
    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|\Illuminate\Database\Eloquent\Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        return [
            'errors' => $faker->sentence,
        ];
    }
}
