<?php

namespace App\Http\Controllers\Business;

use App\Deposit;
use App\Payment;
use App\PaymentQueue;

class ReportsController extends BaseController
{
    public function deposits()
    {
        $deposits = Deposit::where('business_id', $this->business()->id)
            ->orderBy('created_at', 'DESC')
            ->get();
        return view('business.reports.deposits', compact('deposits'));
    }

    public function payments()
    {
        $month_sum = Payment::where('business_id', $this->business()->id)
                            ->where('created_at', '>', date('Y-m-d H:i:s', strtotime(date('Y-m').'-01')))
                             ->sum('business_allotment');
        $year_sum = Payment::where('business_id', $this->business()->id)
                            ->where('created_at', '>', date('Y-m-d H:i:s', strtotime(date('Y').'-01-01')))
                            ->sum('business_allotment');
        $scheduled_sum = PaymentQueue::where('business_id', $this->business()->id)
                             ->sum('business_allotment');
        $payments = Payment::where('business_id', $this->business()->id)
                           ->orderBy('created_at', 'DESC')
                           ->get()
                           ->map(function(Payment $payment) {
                                return [
                                    'id' => $payment->id,
                                    'client_name' => ($payment->client) ? $payment->client->lastname . ', ' . $payment->client->firstname : '',
                                    'amount' => $payment->amount,
                                    'business_allotment' => $payment->business_allotment,
                                    'success' => $payment->success,
                                    'date' => $payment->created_at->format(\DateTime::ISO8601),
                                ];
                            });
        return view('business.reports.payments', compact('payments', 'month_sum', 'year_sum', 'scheduled_sum'));
    }

    public function scheduled()
    {
        $month_sum = Payment::where('business_id', $this->business()->id)
                            ->where('created_at', '>', date('Y-m-d H:i:s', strtotime('first day of this month 00:00:00')))
                            ->sum('business_allotment');
        $year_sum = Payment::where('business_id', $this->business()->id)
                           ->where('created_at', '>', date('Y-m-d H:i:s', strtotime('first day of this year 00:00:00')))
                           ->sum('business_allotment');
        $scheduled_sum = PaymentQueue::where('business_id', $this->business()->id)
                                     ->sum('business_allotment');
        $payments = PaymentQueue::where('business_id', $this->business()->id)
                                ->orderBy('created_at', 'DESC')
                                ->get()
                                ->map(function(PaymentQueue $payment) {
                                    return [
                                        'id' => $payment->id,
                                        'client_name' => ($payment->client) ? $payment->client->lastname . ', ' . $payment->client->firstname : '',
                                        'amount' => $payment->amount,
                                        'business_allotment' => $payment->business_allotment,
                                        'success' => $payment->success,
                                        'date' => $payment->created_at->format(\DateTime::ISO8601),
                                    ];
                                });
        return view('business.reports.scheduled', compact('payments', 'month_sum', 'year_sum', 'scheduled_sum'));
    }

    public function shifts()
    {
        $shifts = $this->business()
            ->shifts()
            ->whereNotNull('checked_out_time')
            ->orderBy('checked_in_time', 'DESC')
            ->get();
        $shifts = $shifts->map(function($shift) {
            $shift->client_name = ($shift->client) ? $shift->client->name() : '';
            $shift->caregiver_name = ($shift->caregiver) ? $shift->caregiver->name() : '';
            return $shift;
        });
        return view('business.reports.shifts', compact('shifts'));
    }
}
