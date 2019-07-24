<?php

namespace App\Reports;

use App\Billing\Payment;
use App\Billing\Queries\PaymentQuery;
use Carbon\Carbon;

class TotalChargesReport extends BaseReport
{

    /**
     * @var string
     */
    protected $timezone;

    /**
     * @var string
     */
    protected $start;

    /**
     * @var string
     */
    protected $end;

    /**
     * @var PaymentQuery
     */
    protected $query;

    /**
     * TotalChargesReport constructor.
     */
    public function __construct(PaymentQuery $query)
    {
        $this->query = $query->with('business');
    }

    public function setTimezone($timezone): self
    {

        $this->timezone = $timezone;

        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query(): self
    {
        return $this;
    }

    /**
     * @param string $date
     * @return TotalChargesReport
     */
    public function applyFilters(string $date): self
    {
        $this->start = (new Carbon($date . ' 00:00:00', 'UTC'));
        $this->end = (new Carbon($date . ' 23:59:59', 'UTC'));

        $this->query->whereBetween('created_at', [$this->start, $this->end]);

        return $this;

    }

    /**
     * @return Collection
     */
    protected function results(): iterable
    {
        return $this->query->get()->map(function(Payment $payment){
            return [
                'business'=>$payment->business_allotment,
                'caregiver'=>$payment->caregiver_allotment,
                'system'=>$payment->system_allotment,
                'amount'=>$payment->amount,
                'location'=>filled($payment->business->name) ? $payment->business->name : "Adjustment"
            ];
        });
    }


}