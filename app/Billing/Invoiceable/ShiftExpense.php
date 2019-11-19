<?php
namespace App\Billing\Invoiceable;

use App\Billing\ClientInvoiceItem;
use App\Billing\ClientPayer;
use App\Billing\Invoiceable\Traits\BelongsToThroughShift;
use App\Business;
use App\Caregiver;
use App\Client;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Class ShiftExpense
 * @package App\Billing\Invoiceable
 * @property \App\Shift $shift
 */
class ShiftExpense extends InvoiceableModel
{
    use BelongsToThroughShift;

    protected $guarded = ['id'];
    protected $casts = [
        'shift_id' => 'int',
        'units' => 'float',
        'rate' => 'float',
        'ally_fee' => 'float',
    ];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function shift()
    {
        return $this->belongsTo(\App\Shift::class);
    }

    /////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    /**
     * Collect all applicable invoiceables of this type eligible for the client payment
     *
     * @param \App\Client $client
     * @param \Carbon\Carbon $endDateUtc
     * @return \Illuminate\Support\Collection|\App\Billing\Contracts\InvoiceableInterface[]
     */
    public function getItemsForPayment(Client $client, Carbon $endDateUtc): Collection
    {
        return new Collection([]);  // Handled by App\Shift
    }

    /**
     * Collect all applicable invoiceables of this type eligible for the provider deposit
     *
     * @param \App\Business $business
     * @return \Illuminate\Support\Collection|\App\Billing\Contracts\InvoiceableInterface[]
     */
    public function getItemsForBusinessDeposit(Business $business): Collection
    {
        return new Collection([]); // Shift Expenses are only paid out to caregivers
    }

    /**
     * Get the number of units to be invoiced
     *
     * @return float
     */
    public function getItemUnits(): float
    {
        return $this->units;
    }

    /**
     * Get the name of this item to display on the invoice
     *
     * @param string $invoiceModel
     * @return string
     */
    public function getItemName(string $invoiceModel): string
    {
        return $this->name;
    }

    /**
     * Get the group this item should be listed under on the invoice
     *
     * @param string $invoiceModel
     * @return string|null
     */
    public function getItemGroup(string $invoiceModel): ?string
    {
        return $this->shift->getItemGroup($invoiceModel);
    }

    /**
     * Get the date & time that this item's "service" occurred.   SHOULD respect the client/business timezone.
     * Note: This is used for sorting items on the invoice and determining payer allowances.
     *
     * @return string|null
     */
    public function getItemDate(): ?string
    {
        return $this->shift->getItemDate();
    }

    /**
     * @return string|null
     */
    public function getItemNotes(): ?string
    {
        return str_limit($this->notes, 252);
    }

    /**
     * Check if the client rate includes the ally fee (ex. true for shifts, false for expenses)
     *
     * @return bool
     */
    public function hasFeeIncluded(): bool
    {
        return false;
    }

    public function getShift(): ?Shift
    {
        return $this->shift;
    }

    public function getClient(): ?Client
    {
        return $this->shift->getClient();
    }

    /**
     * Get the client rate of this item (payment rate).  The total charged will be this rate multiplied by the units.
     *
     * @return float
     */
    public function getClientRate(): float
    {
        return $this->rate; // Ally fee not included
    }

    public function getCaregiver(): ?Caregiver
    {
        return $this->shift->getCaregiver();
    }

    /**
     * @return float
     */
    public function getCaregiverRate(): float
    {
        return $this->rate;
    }

    /**
     * Return the ally fee per unit for this invoiceable item.
     * If this returns null, abort deposit invoices.  Return 0.0 for no ally fee.
     *
     * @return float|null
     */
    public function getAllyRate(): ?float
    {
        if ($this->getItemUnits() == 0) {
            return 0.0;
        }
        if ($this->ally_fee === null) {
            return null;
        }
        return round(divide($this->ally_fee, $this->getItemUnits(), 5), 4);
    }

    public function getBusiness(): ?Business
    {
        return $this->shift->getBusiness();
    }

    /**
     * Note: This is a calculated field from the other rates
     * @return float
     */
    public function getProviderRate(): float
    {
        return floatval(0);
    }

    /**
     * Add an amount that has been invoiced to a payer
     *
     * @param \App\Billing\ClientInvoiceItem $invoiceItem
     * @param float $amount
     * @param float $allyFee  The value of $amount that represents the Ally Fee
     */
    public function addAmountInvoiced(ClientInvoiceItem $invoiceItem, float $amount, float $allyFee): void
    {
        if ($this->ally_fee === null) {
            $this->update(['ally_fee' => $allyFee]);
        } else {
            $this->increment('ally_fee', $allyFee);
        }
    }

    /**
     * Get the amount that has been invoiced to the client
     *
     * @return float
     */
    public function getAmountInvoiced(): float
    {
        return (float) $this->clientInvoiceItems()->sum('amount_due') - $this->ally_fee;
    }

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding { getScrubQuery as parentGetScrubQuery; }

    /**
     * Get the query used to identify records that will be scrubbed.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getScrubQuery() : \Illuminate\Database\Eloquent\Builder
    {
        return static::parentGetScrubQuery()->whereNotNull('notes');
    }

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
            'notes' => $faker->sentence,
        ];
    }
}