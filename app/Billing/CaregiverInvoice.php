<?php
namespace App\Billing;

use App\AuditableModel;
use App\Billing\Contracts\DepositInvoiceInterface;
use App\Billing\Events\InvoiceableDepositAdded;
use App\Billing\Events\InvoiceableDepositRemoved;
use App\Contracts\ContactableInterface;
use Illuminate\Support\Collection;

/**
 * \App\Billing\CaregiverInvoice
 *
 * @property int $id
 * @property string $name
 * @property int $caregiver_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property float $amount
 * @property float $amount_paid
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Caregiver $caregiver
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\Deposit[] $deposits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Billing\CaregiverInvoiceItem[] $items
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\CaregiverInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\CaregiverInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Billing\CaregiverInvoice query()
 * @mixin \Eloquent
 * @property string|null $notes
 * @property-read int|null $audits_count
 * @property-read int|null $deposits_count
 * @property-read int|null $items_count
 */
class CaregiverInvoice extends AuditableModel implements DepositInvoiceInterface
{
    protected $guarded = ['id'];

    protected $casts = [
        'caregiver_id' => 'int',
        'amount' => 'float',
        'amount_paid' => 'float',
    ];

    /**
     * Get the next invoice name for a client
     *
     * @param int $caregiverId
     * @return string
     */
    public static function getNextName(int $caregiverId)
    {
        $lastName = self::where('caregiver_id', $caregiverId)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->value('name');


        $minId = 1000;
        if (!$lastName) {
            $nextId = $minId;
        } else {
            $nextId = (int) substr($lastName, strpos($lastName, '-') + 1) + 1;
        }

        if ($nextId < $minId) {
            $nextId = $minId;
        }

        return "C${caregiverId}-${nextId}";
    }


    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function caregiver()
    {
        return $this->belongsTo(\App\Caregiver::class);
    }

    function items()
    {
        return $this->hasMany(CaregiverInvoiceItem::class, 'invoice_id');
    }

    function deposits()
    {
        return $this->morphToMany(Deposit::class, 'invoice', 'invoice_deposits')
            ->withPivot(['amount_applied']);
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    function addItem(CaregiverInvoiceItem $item): bool
    {
        if ($this->items()->save($item)) {
            return $this->update(['amount' => $this->items()->sum('total')]);
        }
        return false;
    }

    function addDeposit(Deposit $deposit, float $amountApplied): bool
    {
        if ($this->deposits()->save($deposit, ['amount_applied' => $amountApplied])
            && $this->increment('amount_paid', $amountApplied)) {
            foreach($this->getItems() as $item) {
                if ($item->getInvoiceable()) {
                    event(new InvoiceableDepositAdded($item->getInvoiceable(), $deposit));
                }
            }

            return true;
        }
        return false;
    }

    function removeDeposit(Deposit $deposit): bool
    {
        if (($deposit = $this->deposits->where('id', $deposit->id)->first())
            && $this->deposits()->syncWithoutDetaching([$deposit->id => ['amount_applied' => 0]])
            && $this->decrement('amount_paid', $deposit->pivot->amount_applied)){
            foreach($this->getItems() as $item) {
                if ($item->getInvoiceable()) {
                    event(new InvoiceableDepositRemoved($item->getInvoiceable(), $deposit));
                }
            }

            return true;
        }

        return false;
    }

    public function getAmount(): float
    {
        return (float) $this->amount;
    }

    public function getAmountDue(): float
    {
        return subtract($this->amount, $this->amount_paid);
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    function getItemGroups(): Collection
    {
        return $this->getItems()->sortBy('date')->groupBy('group');
    }

    function getName(): string
    {
        return $this->name;
    }

    function getDate(): string
    {
        return $this->created_at->format('m/d/Y');
    }

    function getAmountPaid(): float
    {
        return (float) $this->amount_paid;
    }

    function getRecipient(): ContactableInterface
    {
        return $this->caregiver;
    }
}