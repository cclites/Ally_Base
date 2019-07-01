<?php


namespace App\Reports;


use App\Billing\ClientInvoice;
use App\Billing\Payer;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Traits\BelongsToOneBusiness;
use Carbon\Carbon;
use App\ShiftConfirmation;

use Log;

//$payers = Payer::forAuthorizedChain()->ordered()->get();

/**
 * Class PayerInvoiceReport
 * @package App\Reports
 */
class PayerInvoiceReport extends BaseReport
{
    use BelongsToOneBusiness;

    /**
     * @var object
     */
    protected $query;

    /**
     * @var int
     */
    protected $payer;

    /**
     * @var boolean
     */
    protected $isConfirmed;

    /**
     * @var boolean
     */
    protected $isCharged;

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */

    public function __construct()
    {
        $this->query = new ClientInvoiceQuery();
    }

    public function query()
    {
        return $this->query;
    }

    /**
     * get invoices for data
     *
     * @param string $start
     * @param string $end
     * @param string|null $timezone
     * @return PayerInvoiceReport
     */
    public function forDates(string $start, string $end, ?string $timezone = null) : self
    {
        if (empty($timezone)) {
            $timezone = 'America/New_York';
        }

        $startDate = new Carbon($start . ' 00:00:00', $timezone);
        $endDate = new Carbon($end . ' 23:59:59', $timezone);
        $this->query->whereBetween('created_at', [$startDate, $endDate]);
        //$this->between($startDate, $endDate);


        return $this;
    }

    /**
     * Set payer id
     *
     * @param int|null $id
     * @return void
     */
    public function forPayer(?int $id = null)
    {
        $this->query->where('client_payer_id', $id);

        return $this;
    }

    /**
     * Set shift is confirmed
     *
     * @param boolean|null $confirmed
     * @return void
     */
    public function isConfirmed(?string $confirmed = null)
    {
        $this->confirmed = $confirmed;
    }

    /**
     * get confirmed shifts
     *
     * @return array
     */
    public function getConfirmed($invoices)
    {
        $collection = collect();

        Log::info("GetConfirmed");

        foreach($invoices as $item){

            //$invoice->

            if($this->isConfirmed == "true"){
                $item->whereHas(ShiftConfirmation::class, function($q) use ($item, $collection){
                    if( $q->where($q->shift_id, $item->shift_id) ){
                        $collection->push($item);
                    }
                });
            }elseif($this->isConfirmed == "false"){
                $item->whereHas(ShiftConfirmation::class, function($q) use ($item, $collection){
                    if( !$q->where($q->shift_id, $item->shift_id) ){
                        $collection->push($item);
                    }
                });
            }else{
                $collection->push($item);
            }
        }

        return $collection;
    }

    /**
     * set shift is charged
     *
     * @param boolean|null $charged
     * @return void
     */
    public function isCharged(?string $charged = null)
    {
        $this->charged = $charged;
    }

    /**
     * get charged shifts
     *
     * @return array
     */
    public function getCharged($invoices){

        Log::info("GetCharged");

        $collection = collect();

        foreach($invoices as $item){

            Log::info($item);

            /*
            if($this->charged == "true" && $item->getAmountCharged() > 0){
                $collection->push($item);
            }elseif($this->charged == 'false' && $item->getAmountCharged() == 0){
                $collection->push($item);
            }
            else{
                $collection->push($item);
            }*/
        }

        $collection->push($item);

        return $collection;

    }



    /**
     * @return Collection
     */
    protected function results() : ?iterable
    {
        $query = clone $this->query;

        $invoices = $query->get()->toArray();
        $invoices = $this->getConfirmed($invoices);
        $invoices = $this->getCharged($invoices);


        return $this->rows;
    }

}