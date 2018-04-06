<?php
namespace App\Reports;

use App\Deposit;
use Carbon\Carbon;

class AdminBucketReport extends BaseReport
{
    protected $startDate;
    protected $endDate;

    function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Return the instance of the query builder for additional manipulation
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return false;
    }

    /**
     * Return the collection of rows matching report criteria
     *
     * @return \Illuminate\Support\Collection
     */
    protected function results()
    {
        $results = [];
        for($date = $this->startDate; $date <= $this->endDate; $date->addDay()) {
            $results[] = $this->depositsForDate($date->toDateString());
        }
        return collect($results);
    }

    /**
     *
     * 1. Get deposits on day
     * 2. Loop through deposits shifts, get
     *
     */

    function depositsForDate($date)
    {
        $deposits = Deposit::with(['shifts', 'shifts.payment', 'shifts.payment.client'])
            ->whereDate('created_at', $date)
            ->get();
        $paymentDates = [];
        $missing = [];
        $failed = [];

        foreach($deposits as $deposit) {
            foreach($deposit->shifts as $shift) {
                if ($deposit->caregiver) {
                    $pAmount = $shift->costs()->getCaregiverCost();
                }
                else {
                    $pAmount = $shift->costs()->getProviderFee();
                }
                if (!$shift->payment) {
                    $pDate = 'missing';
                    $missing[] = $shift;
                }
                else if (!$shift->payment->success) {
                    $pDate = 'failed';
                    $failed[] = $shift;
                }
                else {
                    $pDate = $shift->payment->created_at->toDateString();
                }

                $left = $paymentDates[$pDate] ?? 0;
                $right = $pAmount;
                $paymentDates[$pDate] = bcadd($left, $right, 2);
            }
        }

        return [
            'date' => $date,
            'count' => $deposits->count(),
            'sum' => $deposits->sum('amount'),
            'payment_dates' => $paymentDates,
            'missing' => $missing,
            'failed' => $failed
        ];
    }
}