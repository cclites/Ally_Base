<?php
namespace App\Reports;

use App\Business;
use App\User;
use App\SalesPerson;
use App\BusinessChain;
use App\Clients;
use App\Contracts\BusinessReportInterface;

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

    /**
     * The business ID.
     *
     * @var int
     */
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
        $this->startDate = $start;
        $this->endDate = $end;

        return $this;
    }


    /**
     * @param $businessId
     */
    public function forBusiness($businessId = null)
    {
        $this->businessId = $businessId;
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

        $detail = $this->query
                  ->forSalespersonId($this->salespersonId)
                  ->get();

        return json_encode($detail);

    }


}

