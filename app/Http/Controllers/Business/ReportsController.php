<?php

namespace App\Http\Controllers\Business;

use App\Deposit;
use App\Payment;
use App\PaymentQueue;
use App\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportsController extends BaseController
{
    public function overtime(Request $request)
    {
        if (!$offset = $request->input('offset')) {
            $offset = "America/New_York";
        }

        if (!$week = $request->input('week')) {
            $week = Carbon::now($offset)->weekOfYear;
        }

        if (!$year = $request->input('year')) {
            $year = Carbon::now($offset)->year;
        }

        $weekStart = (new Carbon())->setISODate($year, $week, 1)->setTime(0,0,0);
        $weekEnd = (new Carbon())->setISODate($year, $week, 7)->setTime(23,59,59);
        $caregivers = [];

        foreach($this->business()->caregivers as $caregiver) {

            $hours = [
                'user' => $caregiver->user,
                'worked' => 0,
                'scheduled' => 0,
            ];

            // Calculate total number of hours in finished shifts
            $caregiver->shifts()->whereBetween('checked_in_time', [$weekStart, $weekEnd])
                ->whereNotNull('checked_out_time')->get()
                ->each(function($shift) use ($hours) {
                    $hours['worked'] += $shift->duration();
                });

            // Calculate number of hours in current shift
            $lastShiftEnd = new Carbon();
            $caregiver->shifts()->whereBetween('checked_in_time', [$weekStart, $weekEnd])
                ->whereNull('checked_out_time')->get()
                ->each(function($shift) use ($hours, $lastShiftEnd) {
                    $hours['worked'] += $shift->duration();
                    $hours['scheduled'] += $shift->remaining();
                    $lastShiftEnd = $shift->scheduledEndTime();
                });

            // Calculate number of hours in future shifts
            $events = $caregiver->getEvents($lastShiftEnd, $weekEnd);
            foreach($events as $event) {
                $schedule = Schedule::find($event['schedule_id']);
                $hours['scheduled'] += round($schedule->duration / 60, 2);
            }

            $hours['total'] = $hours['scheduled'] + $hours['worked'];

            // Aggregate
            $caregivers[] = $hours;
        }

        return view('business.reports.overtime', compact('caregivers'));
    }

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
