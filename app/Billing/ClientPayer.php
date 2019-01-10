<?php
namespace App\Billing;

use App\AuditableModel;
use App\Client;
use App\Billing\Contracts\ChargeableInterface;
use Carbon\Carbon;

/**
 * App\Billing\ClientPayer
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Client $client
 * @property-read \App\Billing\Payer $payer
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @mixin \Eloquent
 */
class ClientPayer extends AuditableModel
{
    protected $orderedColumn = 'priority';
    protected $guarded = ['id'];
    protected $with = ['payer'];

    public static $allowanceTypes = [
        'daily', 'weekly', 'monthly'
    ];

    /**
     * @var array
     */
    protected $newInvoiceAmounts = [];

    ////////////////////////////////////
    //// Relationship Methods
    ////////////////////////////////////

    function client()
    {
        return $this->belongsTo(Client::class);
    }

    function payer()
    {
        return $this->belongsTo(Payer::class);
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    /**
     * Get the payment method for this payer
     *
     * @return \App\Billing\Contracts\ChargeableInterface
     */
    function getPaymentMethod(): ChargeableInterface
    {
        if ($this->payer_id === null) {
            // Private pay
            return $this->client->getPaymentMethod();
        }

        // Fall back to provider pay for all other payments.
        return $this->client->business;
    }

    /**
     * Get the starting day of the week for this payer (0 = Sunday, 6 = Saturday)
     *
     * @return int
     */
    function getStartOfWeek(): int
    {
        return 1;
    }

    /**
     * Get the current payment allowance for a specific date
     *
     * @param string $date
     * @return float
     */
    function getAllowance(string $date): float
    {
        $date = Carbon::parse($date);

        switch($this->payment_allocation) {
            case 'daily':
                $firstDay = $date->toDateString();
                $lastDay = $date->toDateString();
                break;
            case 'weekly':
                $firstDay = $date->copy()->startOfWeek()->addDays($this->getStartOfWeek() - 1)->toDateString();
                $lastDay = $date->copy()->endOfWeek()->addDays($this->getStartOfWeek() - 1)->toDateString();
                break;
            case 'monthly':
                $firstDay = $date->copy()->startOfMonth()->toDateString();
                $lastDay = $date->copy()->endOfMonth()->toDateString();
                break;
            default:
                return 9999999.99;  // No allowance (just a high float that is inconceivable for a single invoice)
        }

        // Calculate data from existing invoice items in database
        $currentSum = InvoiceItem::whereHas('invoice', function ($invoice) {
            $invoice->where('client_id', $this->client_id)->where('payer_id', $this->payer_id);
        })
            ->whereBetween('date', [$firstDay . ' 00:00:00', $lastDay . ' 23:59:59'])
            ->sum('amount_due') ?? 0;

        // Calculate data from newly added data (addAmountInvoiced)
        $currentDay = $firstDay;
        do {
            $currentSum = bcadd($currentSum, $this->newInvoiceAmounts[$currentDay] ?? 0, 2);
            $currentDay = Carbon::parse($currentDay)->addDay()->toDateString();
        }
        while ($currentDay < $lastDay);

        return (float) $currentSum;
    }

    /**
     * Add a new amount that has been invoiced, this is used to track data not yet persisted in invoice items
     *
     * @param float $amount
     * @param string $date
     */
    function addAmountInvoiced(float $amount, string $date): void
    {
        $this->newInvoiceAmounts[$date] = bcadd($this->newInvoiceAmounts[$date] ?? 0, $amount, 2);
    }
}