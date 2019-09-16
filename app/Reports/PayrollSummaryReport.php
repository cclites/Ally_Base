<?php


namespace App\Reports;


use App\Billing\CaregiverInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Deposit;

use App\Billing\Queries\CaregiverInvoiceQuery;
use App\Business;
use Carbon\Carbon;
use App\Client;


class PayrollSummaryReport extends BusinessResourceReport
{
    /**
     * @var \Eloquent
     */
    protected $query;

    /**
     * @var string
     */
    protected $timezone;

    /**
     * @var string
     */
    protected $clientType;


    /**
     * PayrollSummaryReport constructor.
     */
    public function __construct(CaregiverInvoiceQuery $query)
    {
        $this->query = $query
                        ->with([
                            'caregiver',
                            'caregiver.clients',
                            'items',
                            'deposits'
                        ]);
    }


    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query(): self
    {
        return $this->query;
    }

    /**
     * Set instance timezone
     *
     * @param string $timezone
     * @return PayrollSummaryReport
     */
    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function applyFilters(string $start, string $end, int $business, ?string $client_type, ?int $caregiver): self
    {
        $startDate = (new Carbon($start . ' 00:00:00', $this->timezone))->setTimezone('UTC');
        $endDate = (new Carbon($end . ' 23:59:59', $this->timezone))->setTimezone('UTC');
        $this->query->whereBetween('created_at', [$startDate, $endDate]);

        $this->query->whereHas('caregiver', function($q) use($business){
            $q->forBusinesses([$business]);
        });

        if(filled($client_type)){
            $this->clientType = $client_type;
            $this->query->whereHas('caregiver.clients', function($q) use($client_type){
                $q->where('client_type', $client_type);
            });
        }

        if(filled($caregiver)){
            $this->query->whereCaregiverId($caregiver);
        }

        return $this;
    }

    protected function results() : ?iterable
    {
          return $this->query->get()->map(function(CaregiverInvoice $invoice){
                return [
                        'amount'=>$invoice->amount,
                        'caregiver'=>$invoice->caregiver->nameLastFirst(),
                        'type'=>$this->clientType ? ucwords(str_replace("_", " ", $this->clientType)) : 'All Types',
                        'date'=> (new Carbon($invoice->created_at))->format('m/d/Y'),
                        'invoice_items' => $invoice->items,
                        'deposits' => $invoice->deposits->first(),
                    ];
          });

    }
}