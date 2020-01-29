<?php

namespace App\Claims;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ScrubsForSeeding;
use App\AuditableModel;

/**
 * App\Claims\ClaimAdjustment
 *
 * @property int $id
 * @property int $claim_remit_id
 * @property int|null $claim_invoice_id
 * @property int|null $claim_invoice_item_id
 * @property string $adjustment_type
 * @property float $amount_applied
 * @property int $is_interest
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Claims\ClaimInvoice|null $claimInvoice
 * @property-read \App\Claims\ClaimInvoiceItem|null $claimInvoiceItem
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimAdjustment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimAdjustment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimAdjustment query()
 * @mixin \Eloquent
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read \App\Claims\ClaimRemit|null $remit
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Claims\ClaimAdjustment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\Claims\ClaimAdjustment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Claims\ClaimAdjustment withoutTrashed()
 */
class ClaimAdjustment extends AuditableModel
{
    use SoftDeletes;

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

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the ClaimInvoice relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function claimInvoice()
    {
        return $this->belongsTo(ClaimInvoice::class);
    }

    /**
     * Get the ClaimInvoiceItem relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function claimInvoiceItem()
    {
        return $this->belongsTo(ClaimInvoiceItem::class);
    }

    /**
     * Get the ClaimRemit relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function remit()
    {
        return $this->belongsTo(ClaimRemit::class, 'claim_remit_id');
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use ScrubsForSeeding {
        getScrubQuery as parentGetScrubQuery;
    }

    /**
     * Get the query used to identify records that will be scrubbed.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getScrubQuery(): Builder
    {
        return static::parentGetScrubQuery()->whereNotNull('note');
    }

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item): array
    {
        return [
            'note' => $faker->sentence,
        ];
    }
}
