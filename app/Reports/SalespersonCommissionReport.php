<?php
namespace App\Reports;

use App\SalesPerson;
use App\Client;
use App\Traits\IsUserRole;


use Carbon\Carbon;
use Log;

use Illuminate\Http\Request;


class SalespersonCommissionReport extends BusinessResourceReport {

    /**
     * The begin date.
     *
     * @var string
     */
    protected $startDate;

    /**
     * The end date.
     *
     * @var string
     */
    protected $endDate;

    /**
     * The salesperson ID.
     *
     * @var int
     */
    protected $salespersonId;

    protected $businessId;

    public function __construct()
    {
        $this->query = SalesPerson::query();
    }

    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return $this->query;
    }



    /**
     * Filter the results to between two dates.
     *
     * @param string $start
     * @param string $end
     * @return $this
     */
    public function forDates($start, $end)
    {

        //format the date
        $timezone = activeBusiness()->timezone;
        $startDate = Carbon::parse($start . ' 00:00:00', $timezone)->setTimezone('UTC')->toDateTimeString();
        $endDate = Carbon::parse($end . ' 23:59:59', $timezone)->setTimezone('UTC')->toDateTimeString();

        $this->startDate = $startDate;
        $this->endDate = $endDate;
        return $this;
    }

    public function forBusinessId($id){
        $this->businessId = $id;
        return $this;
    }
    /**
     * @param $salespersonId
     */
    public function forSalespersonId($salespersonId = null)
    {
        $this->salespersonId = $salespersonId;
        return $this;
    }

    protected function results()
    {
        //get salespeople for business
        if (filled($this->salespersonId)) {
            $this->query->where('sales_people.id', $this->salespersonId)
                 ->whereIn('sales_people.business_id', $this->businessId);
        }else{
            $this->query->whereIn('sales_people.business_id', $this->businessId);
        }

        $salespeople =  $this->query->get();

        //get client counts for each salesperson and append
        foreach($salespeople as $salesperson){

            $clients = Client::where('sales_person_id', $salesperson->id)
                               ->whereHas('user', function($q){
                                   $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
                               })
                               ->get()->toArray();

            $salesperson['clientCount'] = count($clients);

        }

        //reduce the amount of information being sent to the view
        $salespeople = $salespeople->map(function($item){
                          return [
                              'text'=>$item->firstname . " " . $item->lastname,
                              'clients' => $item->clientCount
                          ];
                       });


        return $salespeople;
    }
}

