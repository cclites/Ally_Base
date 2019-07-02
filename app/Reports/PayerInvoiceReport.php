<?php


namespace App\Reports;


use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Payer;
use App\Billing\Queries\ClientInvoiceQuery;
use Carbon\Carbon;
use App\ShiftConfirmation;
use App\Shift;
use App\Client;

use Illuminate\Database\Eloquent\Builder;
use Log;

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
     * @param string $startDate
     * @param string $endDate
     * @param int|null $payerId
     * @param int|null $businessId
     * @param string|null $confirmed
     * @param string|null $charged
     * @return PayerInvoiceReport
     */
    public function applyFilters(string $startDate, string $endDate, ?int $payerId, int $businessId, ?string $confirmed, ?string $charged) : self
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

        if (filled($charged)) {
            if ($charged === 'true') {
                $this->query->whereHas('items', function($q){
                    return $q->getShift()->whereReadOnly();
                });
            } elseif ($charged === 'false') {
                $this->query->whereHas('items', function($q){
                    return $q->getShift()->wherePending();
                });
            }
        }

        if (filled($confirmed)) {
            if ($confirmed === 'false') {

                $this->query->whereHas('items', function($q){
                    return $q->getShift()->where('status', Shift::WAITING_FOR_CONFIRMATION);
                });
            }
            elseif($confirmed === 'true') {
                $this->query->whereHas('items', function($q){
                    return $q->getShift()->whereNotIn('status',  [Shift::WAITING_FOR_CONFIRMATION, Shift::CLOCKED_IN]);
                });
            }
        }

        return $this;


    }



    /**
     * @return Collection
     */
    protected function results() : ?iterable
    {
        $items = $this->query->get();

        foreach ($items as $item) {
            Log::info($item);
            Log::info("\n\n");
        }

        return $items;


    }

}