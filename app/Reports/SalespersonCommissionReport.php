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
     * @param $salespersonId
     */
    public function forSalespersonId($salespersonId = null)
    {
        $this->salespersonId = $salespersonId;
        return $this;
    }

    protected function results()
    {
        if (filled($this->salespersonId)) {
            $this->query->where('id', $this->salespersonId);
        }

        return $this->query->get();
    }
}

