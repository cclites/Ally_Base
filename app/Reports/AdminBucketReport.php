<?php
namespace App\Reports;

use App\Deposit;
use App\Payment;
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
            $deposits = $this->depositsForDate($date->toDateString());
            $payments = $this->paymentsForDate($date->toDateString());
            $results[] = $deposits + $payments;
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
            ->where('success', 1)
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
            'deposit_count' => $deposits->count(),
            'deposit_sum' => $deposits->sum('amount'),
            'payment_dates' => $paymentDates,
            'missing' => $missing,
            'failed' => $failed
        ];
    }

    function paymentsForDate($date)
    {
        $payment = Payment::where('success', 1)
            ->select(\DB::raw('count(*) as payment_count, sum(amount) as payment_sum'))
            ->whereDate('created_at', $date)
            ->first();

        return $payment->toArray();
    }
}