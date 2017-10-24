<?php

namespace App\Http\Controllers\Business;

use App\Caregiver;
use App\Deposit;
use App\Payment;
use App\PaymentQueue;
use App\Reports\ScheduledPaymentsReport;
use App\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportsController extends BaseController
{
    public function medicaid(Request $request)
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

        $shifts = $this->business()->shifts()
            ->whereBetween('checked_in_time', [$weekStart, $weekEnd])
            ->whereNotNull('checked_out_time')
            ->whereHas('client', function($q) {
                $q->where('client_type', 'medicaid');
            })->get();

        // Calculate total hours worked for Medicaid clients
        $hours = '0';
        foreach($shifts as $shift) {
            $hours = bcadd($hours, $shift->duration(), 2);
        }

        // Calculate total ally fee
        $totalAllyFee = '0';
        foreach($shifts as $shift) {
            $totalAllyFee = bcadd($totalAllyFee, $shift->costs()->getAllyFee(), 2);
        }

        // Calculate total owed
        $totalOwed = '0';
        foreach($shifts as $shift) {
            $totalOwed = bcadd(
                $totalOwed,
                bcadd($shift->costs()->getAllyFee(), $shift->costs()->getCaregiverCost(), 2),
                2
            );
        }

        // Calculate caregiver totals
        $caregivers = [];
        $groupedByCaregiver = $shifts->groupBy('caregiver_id');
        foreach($groupedByCaregiver as $caregiver_id => $caregiverShifts) {
            $caregiver = Caregiver::with('user')->find($caregiver_id);
            $caregiver = [
                'id' => $caregiver->id,
                'firstname' => $caregiver->user->firstname,
                'lastname' => $caregiver->user->lastname,
                'hours' => '0',
                'wages' => '0',
                'provider_fee' => '0',
                'ally_fee' => '0',
            ];
            foreach($caregiverShifts as $shift) {
                $caregiver['hours'] = bcadd($caregiver['hours'], $shift->duration(), 2);
                $caregiver['wages'] = bcadd($caregiver['wages'], $shift->costs()->getCaregiverCost(), 2);
                $caregiver['provider_fee'] = bcadd($caregiver['provider_fee'], $shift->costs()->getProviderFee(), 2);
                $caregiver['ally_fee'] = bcadd($caregiver['ally_fee'], $shift->costs()->getAllyFee(), 2);
            }
            $caregivers[] = $caregiver;
        }

        return view('business.reports.medicaid', compact('hours', 'totalAllyFee', 'totalOwed', 'caregivers'));
    }

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
        $year_start = date('Y-m-d H:i:s', strtotime('first day of this year 00:00:00'));

        $month_sum = Payment::where('business_id', $this->business()->id)
                            ->where('created_at', '>=', $year_start)
                             ->sum('business_allotment');
        $year_sum = Payment::where('business_id', $this->business()->id)
                            ->where('created_at', '>=', $year_start)
                            ->sum('business_allotment');

        $report = new ScheduledPaymentsReport();
        $report->between($year_start, null);
        $scheduled_sum = $report->sum('business_allotment');

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
        $year_start = date('Y-m-d H:i:s', strtotime('first day of this year 00:00:00'));
        $month_sum = Payment::where('business_id', $this->business()->id)
                            ->where('created_at', '>=', $year_start)
                            ->sum('business_allotment');
        $year_sum = Payment::where('business_id', $this->business()->id)
                           ->where('created_at', '>=', $year_start)
                           ->sum('business_allotment');

        $report = new ScheduledPaymentsReport();
        $scheduled_sum = $report->sum('business_allotment');
        $payments = $report->rows();
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
