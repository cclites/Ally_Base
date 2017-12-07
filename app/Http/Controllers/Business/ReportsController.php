<?php

namespace App\Http\Controllers\Business;

use App\Client;
use App\Caregiver;
use App\CreditCard;
use App\Deposit;
use App\GatewayTransaction;
use App\Payment;
use App\Reports\CaregiverPaymentsReport;
use App\Reports\CertificationExpirationReport;
use App\Reports\ClientCaregiversReport;
use App\Reports\ClientChargesReport;
use App\Reports\ProviderReconciliationReport;
use App\Reports\ScheduledPaymentsReport;
use App\Reports\ScheduledVsActualReport;
use App\Reports\ShiftsReport;
use App\Schedule;
use App\Traits\ActiveBusiness;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportsController extends BaseController
{
    use ActiveBusiness;

    public function medicaidReport(Request $request)
    {
        return view('business.reports.medicaid', $this->medicaidData($request));
    }

    public function medicaid(Request $request)
    {
        return response()->json($this->medicaidData($request));
    }

    private function medicaidData(Request $request)
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

        $dates = (object) [
            'start' => (new Carbon())->setISODate($year, $week, 1)->setTime(0, 0, 0),
            'end' => (new Carbon())->setISODate($year, $week, 7)->setTime(23, 59, 59)
        ];

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $dates = (object) [
                'start' => Carbon::parse($request->start_date),
                'end' => Carbon::parse($request->end_date)
            ];
        }

        $report = new ShiftsReport();

        $report->query()->where('business_id', $this->business()->id)
            ->whereBetween('checked_in_time', [$dates->start, $dates->end])
            ->whereNotNull('checked_out_time')
            ->whereHas('client', function ($q) {
                $q->where('client_type', 'medicaid');
            });

        $shifts = $report->rows();

        $totals = [
            'hours' => $shifts->sum('duration'),
            'ally_fee' => $shifts->sum('ally_fee'),
            'owed' => $shifts->sum('ally_fee') +
                $shifts->reduce(function ($carry, $shift) {
                    return $carry + $shift['duration'] * $shift['caregiver_rate'];
                })
        ];

        return compact('totals', 'shifts', 'dates');
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

        $weekStart = (new Carbon())->setISODate($year, $week, 1)->setTime(0, 0, 0);
        $weekEnd = (new Carbon())->setISODate($year, $week, 7)->setTime(23, 59, 59);
        $caregivers = [];

        foreach ($this->business()->caregivers as $caregiver) {

            $hours = [
                'user' => $caregiver->user,
                'worked' => 0,
                'scheduled' => 0,
            ];

            // Calculate total number of hours in finished shifts
            $caregiver->shifts()->whereBetween('checked_in_time', [$weekStart, $weekEnd])
                ->whereNotNull('checked_out_time')->get()
                ->each(function ($shift) use ($hours) {
                    $hours['worked'] += $shift->duration();
                });

            // Calculate number of hours in current shift
            $lastShiftEnd = new Carbon();
            $caregiver->shifts()->whereBetween('checked_in_time', [$weekStart, $weekEnd])
                ->whereNull('checked_out_time')->get()
                ->each(function ($shift) use ($hours, $lastShiftEnd) {
                    $hours['worked'] += $shift->duration();
                    $hours['scheduled'] += $shift->remaining();
                    $lastShiftEnd = $shift->scheduledEndTime();
                });

            // Calculate number of hours in future shifts
            $events = $caregiver->getEvents($lastShiftEnd, $weekEnd);
            foreach ($events as $event) {
                $schedule = Schedule::find($event['schedule_id']);
                $hours['scheduled'] += round($schedule->duration / 60, 2);
            }

            $hours['total'] = $hours['scheduled'] + $hours['worked'];

            // Aggregate
            $caregivers[] = $hours;
        }

        return view('business.reports.overtime', compact('caregivers'));
    }

    public function reconciliation(Request $request)
    {
        if ($request->expectsJson() && $request->input('json')) {
            $report = new ProviderReconciliationReport($this->business());
            return $report->rows();
        }

        return view('business.reports.reconciliation');
    }

    public function deposits()
    {
        $deposits = Deposit::where('business_id', $this->business()->id)
            ->where('deposit_type', 'business')
            ->orderBy('created_at', 'DESC')
            ->get();
        return view('business.reports.deposits', compact('deposits'));
    }

    public function payments()
    {
        $year_start = date('Y-m-d H:i:s', strtotime('first day of this year 00:00:00'));
        $month_start = date('Y-m-d H:i:s', strtotime('first day of this year 00:00:00'));

        $month_sum = Payment::where('business_id', $this->business()->id)
            ->where('created_at', '>=', $month_start)
            ->sum('business_allotment');
        $month_sum = number_format($month_sum, 2);
        $year_sum = Payment::where('business_id', $this->business()->id)
            ->where('created_at', '>=', $year_start)
            ->sum('business_allotment');
        $year_sum = number_format($year_sum, 2);

        $report = new ScheduledPaymentsReport();
        $report->where('business_id', $this->business()->id)
            ->between($year_start, null);
        $scheduled_sum = $report->sum('business_allotment');
        $scheduled_sum = number_format($scheduled_sum, 2);

        $payments = Payment::where('business_id', $this->business()->id)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->map(function (Payment $payment) {
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
        $month_sum = number_format($month_sum, 2);

        $year_sum = Payment::where('business_id', $this->business()->id)
            ->where('created_at', '>=', $year_start)
            ->sum('business_allotment');
        $year_sum = number_format($year_sum, 2);

        $report = new ScheduledPaymentsReport();
        $report->where('business_id', $this->business()->id);
        $scheduled_sum = $report->sum('business_allotment');
        $scheduled_sum = number_format($scheduled_sum, 2);

        $payments = $report->rows();
        return view('business.reports.scheduled', compact('payments', 'month_sum', 'year_sum', 'scheduled_sum'));
    }

    public function shiftsReport()
    {
        return view('business.reports.shifts');
    }

    public function certificationExpirations(Request $request)
    {
        $defaultDate = new Carbon('now +30 days');

        $caregivers = $this->business()->caregivers;
        $caregiverIds = $caregivers->pluck('id');

        $report = new CertificationExpirationReport();
        $report->orderBy('expires_at');
        $report->between(Carbon::now(), $defaultDate);
        $report->query()->whereIn('caregiver_id', $caregiverIds->toArray());
        $certifications = $report->rows();

        return view('business.reports.certifications', compact('certifications'));
    }

    public function shifts(Request $request)
    {
        $report = new ShiftsReport();
        $report->where('business_id', $this->business()->id);

        if ($request->has('start_date') || $request->has('end_date')) {
            $startDate = new Carbon($request->input('start_date') . ' 00:00:00', $this->business()->timezone);
            $endDate = new Carbon($request->input('end_date') . ' 23:59:59', $this->business()->timezone);
            $report->between($startDate, $endDate);
        }
        if ($request->has('transaction_id')) {
            $report->forTransaction(GatewayTransaction::findOrFail($request->input('transaction_id')));
        }
        if ($caregiver_id = $request->input('caregiver_id')) {
            $report->where('caregiver_id', $caregiver_id);
        }
        if ($client_id = $request->input('client_id')) {
            $report->where('client_id', $client_id);
        }

        return $report->rows();
    }

    public function caregiverPayments(Request $request)
    {
        $startDate = new Carbon($request->input('start_date') . ' 00:00:00', $this->business()->timezone);
        $endDate = new Carbon($request->input('end_date') . ' 23:59:59', $this->business()->timezone);

        $report = new CaregiverPaymentsReport();
        $report->where('business_id', $this->business()->id)->between($startDate, $endDate);

        if ($caregiver_id = $request->input('caregiver_id')) {
            $report->where('caregiver_id', $caregiver_id);
        }
        if ($client_id = $request->input('client_id')) {
            $report->where('client_id', $client_id);
        }


        return $report->rows();
    }

    public function clientCharges(Request $request)
    {
        $startDate = new Carbon($request->input('start_date') . ' 00:00:00', $this->business()->timezone);
        $endDate = new Carbon($request->input('end_date') . ' 23:59:59', $this->business()->timezone);

        $report = new ClientChargesReport();
        $report->where('business_id', $this->business()->id)->between($startDate, $endDate);

        if ($caregiver_id = $request->input('caregiver_id')) {
            $report->where('caregiver_id', $caregiver_id);
        }
        if ($client_id = $request->input('client_id')) {
            $report->where('client_id', $client_id);
        }

        return $report->rows();
    }

    public function scheduledVsActual(Request $request)
    {
        if ($request->expectsJson() && $request->input('json')) {
            $startDate = new Carbon($request->input('start_date') . ' 00:00:00', $this->business()->timezone);
            $endDate = new Carbon($request->input('end_date') . ' 23:59:59', $this->business()->timezone);

            $report = new ScheduledVsActualReport($this->business());
            $report->between($startDate, $endDate);
            return $report->rows();
        }
        return view('business.reports.scheduled_vs_actual');
    }

    public function clientCaregivers(Request $request)
    {
        if ($request->expectsJson() && $request->input('json')) {
            $report = new ClientCaregiversReport();
            $report->where('business_id', $this->business()->id);
            return $report->rows();
        }

        return view('business.reports.client_caregivers');
    }

    /**
     * Get a list of all clients that are missing an email address
     */
    public function clientEmailMissing()
    {
        $clients = Client::whereHas('user', function ($query) {
            $query->whereNull('email');
        })->get();

        return view('business.reports.client_email_missing', compact('clients'));
    }

    public function creditCardExpiration()
    {
        return view('business.reports.cc_expiration', compact('cards'));
    }

    public function creditCards()
    {
        $report_date = Carbon::now()->addDays(request('daysFromNow'));
        $cards = CreditCard::with('user')
            ->whereIn('user_id', $this->business()->clients()->select('id')->pluck('id'))
            ->get()
            ->filter(function ($card) use ($report_date) {
                return $card->expirationDate->lt($report_date);
            })
            ->map(function ($card) {
                $card->expires_in = Carbon::now()->diffForHumans($card->expirationDate);
                return $card;
            });


        return response()->json($cards);
    }
}
