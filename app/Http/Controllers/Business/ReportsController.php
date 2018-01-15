<?php

namespace App\Http\Controllers\Business;

use App\BankAccount;
use App\Business;
use App\Caregiver;
use App\Client;
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
use App\Shifts\AllyFeeCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportsController extends BaseController
{
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
            return $report->orderBy('created_at', 'DESC')
                          ->rows();
        }

        if ($request->input('export')) {
            $report = new ProviderReconciliationReport($this->business());
            return $report->orderBy('created_at', 'DESC')
                          ->setDateFormat('m/d/Y g:i A', $this->business()->timezone)
                          ->download();
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
        $payments = Payment::where('business_id', $this->business()->id)
            ->whereNotNull('client_id')
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
        return view('business.reports.payments', compact('payments'));
    }

    public function scheduled()
    {
        $year_start = date('Y-m-d H:i:s', strtotime('first day of this year 00:00:00'));

        $date = Carbon::now();
        $dates = collect([
            'start' => $date->startOfMonth()->toDateString(),
            'end' => $date->endOfMonth()->toDateString()
        ]);
        if (request()->filled('start_date') && request()->filled('end_date')) {
            $dates = collect([
                'start' => Carbon::parse(request('start_date')),
                'end' => Carbon::parse(request('end_date'))
            ]);
        }

        $report = new ScheduledPaymentsReport();
        $report->query()->where('business_id', $this->business()->id);
        $scheduled = $report->rows()->sum('business_allotment');
        $report->query()
            ->whereBetween('checked_in_time', $dates->values()->toArray())
            ->when(request()->filled('client_id'), function ($query) {
                return $query->where('client_id', request('client_id'));
            })
            ->when(request()->filled('caregiver_id'), function ($query) {
                return $query->where('caregiver_id', request('caregiver_id'));
            })
            ->orderBy('checked_in_time');
        $payments = $report->rows();

        $totals = [
            'selected' => $payments->sum('business_allotment'),
            'year' => Payment::where('business_id', $this->business()->id)
                ->where('created_at', '>=', $year_start)
                ->sum('business_allotment'),
            'scheduled' => $scheduled
        ];

        $caregivers = $this->business()
            ->caregivers()
            ->select('caregivers.id')
            ->get()
            ->sortBy('nameLastFirst')
            ->values()
            ->all();
        $clients = $this->business()
            ->clients()
            ->select('clients.id')
            ->get()
            ->sortBy('nameLastFirst')
            ->values()
            ->all();

        $response = compact('payments', 'totals', 'dates', 'caregivers', 'clients');
        if (request()->expectsJson()) {
            return response()->json($response);
        }

        return view('business.reports.scheduled', $response);
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
        $report->where('business_id', $this->business()->id)
               ->orderBy('checked_in_time');

        $this->addShiftReportFilters($report, $request);

        if ($request->input('export')) {
            return $report->setDateFormat('m/d/Y g:i A', $this->business()->timezone)
                          ->download();
        }

        return $report->rows();
    }

    public function caregiverPayments(Request $request)
    {
        $report = new CaregiverPaymentsReport();
        $report->where('business_id', $this->business()->id);

        $this->addShiftReportFilters($report, $request);

        return $report->rows();
    }

    public function clientCharges(Request $request)
    {
        $report = new ClientChargesReport();
        $report->where('business_id', $this->business()->id);

        $this->addShiftReportFilters($report, $request);

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

    public function clientOnboardedReport()
    {
        return view('business.reports.client_onboarded');
    }

    public function clientOnboardedData()
    {
        return response()->json($this->business()->clients);
    }

    public function caregiverOnboardedReport()
    {
        return view('business.reports.caregiver_onboarded');
    }

    public function caregiverOnboardedData()
    {
        $caregivers = $this->business()->caregivers;

        return response()->json($caregivers);
    }

    public function caregiversMissingBankAccounts()
    {
        $caregivers = $this->business()
            ->caregivers()
            ->with(['shifts' => function ($query) {
                $query->where('status', 'WAITING_FOR_PAYOUT');
            }])
            ->doesntHave('bankAccount')
            ->get();
        return view('business.reports.caregivers_missing_bank_accounts', compact('caregivers'));
    }

    public function printableSchedule()
    {
        return view('business.reports.printable_schedule');
    }

    protected function addShiftReportFilters($report, Request $request)
    {
        if ($request->has('start_date') || $request->has('end_date')) {
            $startDate = new Carbon($request->input('start_date') . ' 00:00:00', $this->business()->timezone);
            $endDate = new Carbon($request->input('end_date') . ' 23:59:59', $this->business()->timezone);
            $report->between($startDate, $endDate);
        }
        if ($request->has('transaction_id')) {
            $report->forTransaction(GatewayTransaction::findOrFail($request->input('transaction_id')));
        }
        if ($request->has('payment_method')) {
            $method = null;
            switch($request->input('payment_method')) {
                case 'credit_card':
                    $method = CreditCard::class;
                    break;
                case 'bank_account':
                    $method = BankAccount::class;
                    break;
                case 'business':
                    $method = Business::class;
                    break;
            }
            if ($method) $report->forPaymentMethod($method);
        }
        if ($caregiver_id = $request->input('caregiver_id')) {
            $report->where('caregiver_id', $caregiver_id);
        }
        if ($client_id = $request->input('client_id')) {
            $report->where('client_id', $client_id);
        }
    }

    public function exportTimesheets()
    {
        $caregivers = $this->business()->caregivers;
        $clients = $this->business()->clients;
        return view('business.reports.export_timesheets', compact('caregivers', 'clients'));
    }

    public function timesheetData(Request $request)
    {
        $query = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'client_id' => 'nullable|int',
            'caregiver_id' => 'nullable|int',
            'client_type' => 'nullable|string',
            'export_type' => 'required|string'
        ]);

        $start_date = $query['start_date'];
        $end_date = $query['end_date'];

        $client_shift_groups = $this->business()->shifts()
            ->with('activities', 'client', 'caregiver')
            ->whereBetween('checked_in_time', [Carbon::parse($query['start_date']), Carbon::parse($query['end_date'])])
            ->orderBy('checked_in_time')
            ->take(200)
            ->get()
            ->map(function ($shift) {
                $allyFee = AllyFeeCalculator::getHourlyRate($shift->client, null, $shift->caregiver_rate, $shift->provider_fee);
                $shift->ally_fee = number_format($allyFee, 2);
                $shift->hourly_total = number_format($shift->caregiver_rate + $shift->provider_fee + $allyFee, 2);
                $shift->other_expenses = number_format($shift->other_expenses, 2);
                $shift->mileage = number_format($shift->mileage, 2);
                $shift->mileage_costs = number_format($shift->costs()->getMileageCost(), 2);
                $shift->caregiver_total = number_format($shift->costs()->getCaregiverCost(), 2);
                $shift->provider_total = number_format($shift->costs()->getProviderFee(), 2);
                $shift->ally_total = number_format($shift->costs()->getAllyFee(), 2);
                $shift->ally_pct = AllyFeeCalculator::getPercentage($shift->client, null);
                $shift->shift_total = number_format($shift->costs()->getTotalCost(), 2);
                return $shift;
            })
            ->groupBy('client_id');
        return view('business.reports.print.timesheets', compact('client_shift_groups', 'start_date', 'end_date'));
    }
}

