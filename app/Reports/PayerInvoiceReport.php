<?php


namespace App\Reports;

use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class PayerInvoiceReport
 * @package App\Reports
 */
class PayerInvoiceReport extends BaseReport
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function __construct()
    {
        $this->query = ClientInvoice::with([
            'client',
            'items',
            'clientPayer',
        ]);

    }

    /**
     * @param string $timezone
     * @return PayerInvoiceReport
     */
    public function setTimezone(string $timezone) : self
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * @return Builder
     */
    public function query() :\Illuminate\Database\Eloquent\Builder
    {
        return $this->query;
    }

    /**
     * Apply filters to report
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $payerId
     * @param int|null $businessId
     * @param string|null $confirmed
     * @param string|null $charged
     * @return PayerInvoiceReport
     */
    public function applyFilters(string $startDate, string $endDate, int $businessId, ?int $payerId) : self
    {
        $this->query->whereHas('client', function($q) use($businessId){
            return $q->where('business_id', $businessId);
        });

        $start = (new Carbon($startDate . ' 00:00:00', $this->timezone))->setTimezone('UTC');
        $end = (new Carbon($endDate . ' 23:59:59', $this->timezone))->setTimezone('UTC');
        $this->query->whereBetween('created_at', [$start, $end]);


        if (filled($payerId)) {
            $this->query->whereHas('clientPayer', function($q) use($payerId){
               return $q->where('payer_id', $payerId);
            });
        }

        return $this;
    }


    /**
     * Returns a collection based on selected criteria
     *
     * @return Collection
     */
    protected function results() : ?iterable
    {
        $rowItems = collect();

        $this->query->get()->map(function(ClientInvoice $clientInvoice) use ($rowItems) {

            $clientInvoice->items->map(function(ClientInvoiceItem $item) use($clientInvoice, $rowItems){

                $group = $item->group;
                $tuples = explode(":", $group);
                $hrs = $tuples[0] . ":" . $tuples[1] . ":" . $tuples[2];
                $hrsTuples = explode(" ", $hrs);

                $rowItem = [
                    'payer'=>$clientInvoice->clientPayer->payer_name,
                    'client'=>$clientInvoice->client->nameLastFirst(),
                    'caregiver'=>$tuples[3],
                    'hours'=>$hrsTuples[2],
                    'service'=>$item->name,
                    'units'=>$item->units,
                    'date'=>$item->date,
                    'rate'=>$item->rate,
                    'total'=>$item->total,
                    'due'=>$item->amount_due,
                    'charges'=>$item->total,
                ];

                $rowItems->push($rowItem);
            });
         });

         return $rowItems;

    }

}