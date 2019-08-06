<?php

namespace App\Claims;

use Illuminate\Database\Eloquent\Model;

class ClaimInvoice extends Model
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
    public $with = [];

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
     * Get the ClaimInvoiceItems relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function items()
    {
        return $this->hasMany(ClaimInvoiceItem::class, 'claim_invoice_id', 'id');
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
    // STATIC METHODS
    // **********************************************************

    /**
     * Get the next invoice name for a client
     *
     * @param int $businessId
     * @return string
     */
    public static function getNextName(int $businessId)
    {
        $lastName = self::where('business_id', $businessId)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->value('name');

        $minId = 1000;
        if (! $lastName) {
            $nextId = $minId;
        } else {
            $nextId = (int) substr($lastName, strpos($lastName, '-') + 1) + 1;
        }

        if ($nextId < $minId) {
            $nextId = $minId;
        }

        return "${businessId}-${nextId}";
    }
}
