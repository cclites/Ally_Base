<?php

namespace App\Http\Controllers\Business;

use App\ReferralSource;
use App\Shifts\ShiftStatusManager;
use Auth;
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
use App\Responses\ErrorResponse;
use App\Schedule;
use App\Scheduling\ScheduleAggregator;
use App\Shift;
use App\Shifts\AllyFeeCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use App\Reports\EVVReport;

class ReportsController extends BaseController
{

    public function index()
    {
        $data['type'] = $this->business()->type;
        $data['role'] = ['role_type' => Auth::user()->role_type];

        return view('business.reports.index', ['data' => json_encode($data)]);
    }

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

    public function overtime()
    {
        return view('business.reports.overtime');
    }

    public function overtimeData(Request $request, ScheduleAggregator $aggregator)
    {
        $timezone = $this->business()->timezone;

        if (!$week = $request->input('week')) {
            $week = Carbon::now($timezone)->weekOfYear;
        }

        if (!$year = $request->input('year')) {
            $year = Carbon::now($timezone)->year;
        }

        // Define the date range for results
        $weekStart = Carbon::now($timezone)->setISODate($year, $week, 1)->setTime(0, 0, 0);
        $weekEnd = Carbon::now($timezone)->setISODate($year, $week, 7)->setTime(23, 59, 59);
        if ($request->filled('start') && $request->filled('end')) {
            $weekStart = Carbon::parse($request->start, $timezone)->setTime(0, 0, 0);
            $weekEnd = Carbon::parse($request->end, $timezone)->setTime(23, 59, 59);
        }

        // Set date range to UTC for database interaction
        $weekStartUTC = $weekStart->copy()->setTimezone('UTC');
        $weekEndUTC = $weekEnd->copy()->setTimezone('UTC');

        // Pull the list of relevant caregivers to loop through
        $query = $this->business()->caregivers();
        $query->whereHas('shifts', function ($query) use ($weekStartUTC, $weekEndUTC) {
            $query->whereBetween('checked_in_time', [$weekStartUTC, $weekEndUTC]);
        });
        $query->when($request->filled('caregiver_id'), function ($query) use ($request) {
            $query->where('caregiver_id', $request->caregiver_id);
        });
        $caregivers = $query->get();


        // Loop through caregivers, calculate hours, add to $results
        $results = [];
        foreach ($caregivers as $caregiver) {

            // Create a new result template
            $hours = [
                'worked' => 0,
                'scheduled' => 0,
                'total' => 0
            ];

            // Calculate total number of hours in finished shifts
            $shifts = $caregiver->shifts()
                                ->whereBetween('checked_in_time', [$weekStartUTC, $weekEndUTC])
                                ->whereNotNull('checked_out_time')
                                ->get();
            foreach($shifts as $shift) {
                $hours['worked'] += $shift->duration();
            }

            // Calculate number of hours in current shift
            $shifts = $caregiver->shifts()
                                ->whereBetween('checked_in_time', [$weekStartUTC, $weekEndUTC])
                                ->whereNull('checked_out_time')
                                ->get();
            foreach($shifts as $shift) {
                $hours['worked'] += $shift->duration();
                $hours['scheduled'] += $shift->remaining();
            }


            // Calculate number of hours in future shifts
            $schedules = $aggregator->fresh()
                                    ->where('caregiver_id', $caregiver->id)
                                    ->getFutureShifts($weekEndUTC);
            foreach ($schedules as $schedule) {
                $hours['scheduled'] += round($schedule->duration / 60, 2);
            }

            // Calculate total expected hours (still scheduled + already worked)
            $hours['total'] = $hours['scheduled'] + $hours['worked'];

            // Aggregate results
            $results[] = array_merge($caregiver->toArray(), $hours);
        }

//        $timezone = $this->business()->timezone;
//
//        $week = Carbon::now($timezone)->weekOfYear;
//        $year = Carbon::now($timezone)->year;
//        $weekStart = Carbon::now($timezone)->setISODate($year, $week, 1)->setTime(0, 0, 0);
//        $weekEnd = Carbon::now($timezone)->setISODate($year, $week, 7)->setTime(23, 59, 59);
//
//        if ($request->filled('start') && $request->filled('end')) {
//            $weekStart = Carbon::parse($request->start)->setTime(0, 0, 0);
//            $weekEnd = Carbon::parse($request->end)->setTime(23, 59, 59);
//        }
//        $caregivers = $this->business()
//            ->caregivers()
//            ->with('shifts')
//            ->whereHas('shifts', function ($query) use ($weekStart, $weekEnd) {
//                $query->whereBetween('checked_in_time', [$weekStart, $weekEnd]);
//            })
//            ->when($request->filled('caregiver_id'), function ($query) use ($request) {
//                $query->where('caregiver_id', $request->caregiver_id);
//            })
//            ->get();
//        $results = collect([]);
//        foreach ($caregivers as $caregiver) {
//            $user = $caregiver->user;
//            // Calculate total number of hours in finished shifts
//            $worked = $caregiver->shifts->where('checked_out_time', '!=', null)
//                ->reduce(function ($carry, $item) {
//                    return $carry + $item->duration();
//                });
//
//            $lastShiftEnd = new Carbon();
//            $scheduled = round($aggregator->fresh()
//                    ->where('caregiver_id', $caregiver->id)
//                    ->getSchedulesBetween($weekStart, $weekEnd)
//                    ->sum('duration') / 60, 2);
//            // Calculate number of hours in current shift
//            foreach ($caregiver->shifts->where('check_out_time', null) as $shift) {
//                $worked += $shift->duration();
//                //$scheduled += $shift->remaining();
//                //$lastShiftEnd = $shift->scheduledEndTime();
//            }
//
//            $worked = round($worked / 60, 2);
//
////            $schedules = $aggregator->fresh()
////                        ->where('caregiver_id', $caregiver->id)
////                        ->getSchedulesStartingBetween($lastShiftEnd, $weekEnd);
////            foreach ($schedules as $schedule) {
////                $scheduled += round($schedule->duration / 60, 2);
////            }
//
//            $results->push(compact('user', 'worked', 'scheduled'));
//        }
//
        $date_range = [$weekStart->toDateString(), $weekEnd->toDateString()];
        return response()->json(compact('results', 'date_range'));
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
        return view('business.reports.payment-history', compact('payments'));
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
        $activities = $this->business()->allActivities();
        $multiLocation = [
            'multiLocationRegistry' => $this->business()->multi_location_registry,
            'name' => $this->business()->name
        ];

        return view('business.reports.shifts', compact('activities', 'multiLocation'));
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

        return view('business.reports.client-caregiver-rates');
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
        return view('business.reports.credit-card-expiration', compact('cards'));
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

    /**
     * Shows all caregivers missing bank accounts.
     *
     * @return Response
     */
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

    /**
     * Shows all clients missing a payment method.
     *
     * @return Response
     */
    public function clientsMissingPaymentMethods()
    {
        $clients = $this->business()
            ->clients()
            ->with(['shifts' => function ($query) {
                $query->where('status', 'WAITING_FOR_CHARGE');
            }])
            ->whereNull('default_payment_id')
            ->get();

        return view('business.reports.clients-missing-payment-methods', compact('clients'));
    }

    public function printableSchedule()
    {
        return view('business.reports.printable-schedules');
    }

    protected function addShiftReportFilters($report, Request $request)
    {
        if ($request->has('start_date') || $request->has('end_date')) {
            $startDate = new Carbon($request->input('start_date') . ' 00:00:00', $this->business()->timezone);
            $endDate = new Carbon($request->input('end_date') . ' 23:59:59', $this->business()->timezone);
            $report->between($startDate, $endDate);
        }

        if ($request->input('import_id')) {
            $report->where('import_id', $request->import_id);
        }

        if ($request->input('shift_id')) {
            $report->where('id', $request->shift_id);
        }

        if ($request->input('transaction_id')) {
            $transaction = GatewayTransaction::findOrFail($request->input('transaction_id'));
            $report->forTransaction($transaction);
            
            if ($request->input('reconciliation_report')) {
                $report->forReconciliationReport($transaction);
            }
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

        if ($status = $request->input('status')) {
            if ($status === 'charged') {
                $report->query()->whereReadOnly();
            } elseif ($status === 'uncharged') {
                $report->query()->wherePending();
            }
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
        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'client_id' => 'nullable|int',
            'caregiver_id' => 'nullable|int',
            'client_type' => 'nullable|string',
            'export_type' => 'required|string'
        ]);

        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $timezone = $this->business()->timezone;

        $client_shift_groups = $this->clientShiftGroups($data);
        $viewData = compact('client_shift_groups', 'start_date', 'end_date', 'timezone');

        switch ($data['export_type']) {
            case 'pdf':
                $pdf = PDF::loadView('business.reports.print.timesheets', $viewData);
                return $pdf->download('timesheet_export.pdf');
            default:
                return view('business.reports.print.timesheets', $viewData);
        }
    }

    public function printPaymentHistory($caregiver_id, $year)
    {
        Carbon::setWeekStartsAt(Carbon::MONDAY);

        $caregiver = Caregiver::find($caregiver_id);
        $deposits = Deposit::with('shifts')
            ->where('caregiver_id', $caregiver->id)
            ->whereYear('created_at', request()->year)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->map(function ($deposit) {
                $deposit->amount = floatval($deposit->amount);
                $deposit->start = Carbon::instance($deposit->created_at)->subWeek()->startOfWeek()->toDateString();
                $deposit->end = Carbon::instance($deposit->created_at)->subWeek()->endOfWeek()->toDateString();
                return $deposit;
            });
        $business = $this->business();
        $pdf = PDF::loadView('caregivers.reports.print_payment_history', compact('caregiver', 'deposits', 'business'));
        return $pdf->download($year . '_year_summary.pdf');
    }

    public function printPaymentDetails($id, $caregiver_id)
    {
        $deposit = Deposit::find($id);
        $shifts = $this->getPaymentShifts($id, $caregiver_id);
        $business = $this->business();

        if (strtolower(request()->type) == 'pdf') {
            $pdf = PDF::loadView('caregivers.print.payment_details', compact('business', 'shifts', 'deposit'))->setOrientation('landscape');
            return $pdf->download('deposit_details.pdf');
        }

        return view('caregivers.print.payment_details', compact('business', 'shifts', 'deposit'));
    }

    /**
     * @param $id
     * @param $caregiverId
     * @return mixed
     */
    protected function getPaymentShifts($id, $caregiver_id)
    {
        $shifts = Shift::with('deposits', 'activities')
                       ->whereHas('deposits', function ($query) use ($id) {
                           $query->where('deposits.id', $id);
                       })
                       ->where('caregiver_id', $caregiver_id)
                       ->orderBy('checked_in_time')
                       ->get()
                       ->map(function ($shift) {
                           $allyFee = AllyFeeCalculator::getHourlyRate($shift->client, null, $shift->caregiver_rate,
                               $shift->provider_fee);
                           $row = (object)collect($shift->toArray())
                               ->merge([
                                   'hours'           => $shift->duration(),
                                   'ally_fee'        => number_format($allyFee, 2),
                                   'hourly_total'    => number_format($shift->caregiver_rate + $shift->provider_fee + $allyFee,
                                       2),
                                   'mileage_costs'   => number_format($shift->costs()->getMileageCost(), 2),
                                   'caregiver_total' => number_format($shift->costs()->getCaregiverCost(), 2),
                                   'provider_total'  => number_format($shift->costs()->getProviderFee(), 2),
                                   'ally_total'      => number_format($shift->costs()->getAllyFee(), 2),
                                   'ally_pct'        => AllyFeeCalculator::getPercentage($shift->client, null),
                                   'shift_total'     => number_format($shift->costs()->getTotalCost(), 2),
                                   'confirmed'       => $shift->statusManager()->isConfirmed(),
                                   'status'          => $shift->status ? title_case(preg_replace('/_/', ' ',
                                       $shift->status)) : '',
                                   'EVV'             => $shift->verified,
                               ])->toArray();

                           $row->checked_in_time = Carbon::parse($row->checked_in_time);
                           $row->checked_out_time = Carbon::parse($row->checked_out_time);
                           $row->activities = collect($row->activities)->sortBy('name');

                           return $row;
                       });

        return $shifts;
    }

    private function clientShiftGroups(array $data)
    {
        return $this->business()->shifts()
            ->with('activities', 'client', 'caregiver')
            ->whereBetween('checked_in_time', [Carbon::parse($data['start_date']), Carbon::parse($data['end_date'])])
            ->when($data['client_id'], function ($query) use ($data) {
                return $query->where('client_id', $data['client_id']);
            })
            ->when($data['caregiver_id'], function ($query) use ($data) {
                return $query->where('caregiver_id', $data['caregiver_id']);
            })
            ->when($data['client_type'], function ($query) use ($data) {
                return $query->whereHas('client', function ($query) use ($data) {
                    $query->where('client_type', $data['client_type']);
                });
            })
            ->orderBy('checked_in_time')
            //->take(500)
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
    }
    
    /**
     * List of referral sources and how many clients have been referred by each
     *
     * @return Response
     */
    public function referralSources()
    {
        $business = $this->business();
        $reports = [];

        $shiftstatuses = ShiftStatusManager::getPendingStatuses();

        if($business) {
            $referralsources = ReferralSource::where('business_id', $business->id)
                ->withCount('client', 'prospect')->with(['client.shifts' => function($query) use($shiftstatuses){
                    $query->whereNotIn('status', $shiftstatuses)->get();
                }])->get();

            if($referralsources) {
                foreach($referralsources as $referralsource) {
                    $reports[] = [
                        "id" => $referralsource->id,
                        "business_id" => $referralsource->business_id,
                        "organization" => $referralsource->organization,
                        "contact_name" => $referralsource->contact_name,
                        "phone" => $referralsource->phone,
                        "created_at" => Carbon::parse($referralsource->created_at)->format('d/m/Y'),
                        "client_count" => $referralsource->client_count,
                        "prospect_count" => $referralsource->prospect_count,
                        "shift_total" => ($referralsource->client->map(function($item) {
                               return $item->shifts->map(function($shift) {
                                   return number_format($shift->costs()->getTotalCost(), 2);
                               })->sum();
                        }))->sum()
                    ];
                }
            }
        }

        $reports = collect($reports);
        return view('business.reports.referral_sources', compact('reports'));
    }

    /**
     * Shows the list of prospective clients
     *
     * @return Response
     */
    public function prospects()
    {
        return view('business.reports.prospects');
    }

    /**
     * See how many shifts have been worked by a caregiver
     *
     * @return Response
     */
    public function caregiverShifts()
    {
        if (request()->has('fetch')) {
            $report = $this->business()->shifts()
                ->selectRaw('caregiver_id, count(*) as total')
                ->betweenDates(request()->start_date, request()->end_date)
                ->forCaregiver(request()->user_id)
                ->groupBy('caregiver_id')
                ->with('caregiver')
                ->get()
                ->map(function ($item) {
                    return array_merge($item->toArray(), [
                        'name' => $item->caregiver->name,
                        'user_id' => $item->caregiver->id,
                    ]);
                });
            
            return response()->json($report);
        }

        $type = 'caregiver';
        $users = $this->business()->caregiverList();

        return view('business.reports.shift_summary', compact(['type', 'users']));
    }

    /**
     * See how many shifts a client has received
     *
     * @return Response
     */
    public function clientShifts()
    {
        if (request()->has('fetch')) {
            $report = $this->business()->shifts()
                ->selectRaw('client_id, count(*) as total')
                ->betweenDates(request()->start_date, request()->end_date)
                ->forClient(request()->user_id)
                ->groupBy('client_id')
                ->with('client')
                ->get()
                ->map(function ($item) {
                    return array_merge($item->toArray(), [
                        'name' => $item->client->name,
                        'user_id' => $item->client->id,
                    ]);
                });
            
            return response()->json($report);
        }

        
        $type = 'client';
        $users = $this->business()->clientList();

        return view('business.reports.shift_summary', compact(['type', 'users']));
    }

    /**
     * See the onboard status for clients and caregivers and send electronic signup link
     *
     * @return Response
     */
    public function onboardStatus()
    {
        $type = request()->type == 'client' ? 'client' : 'caregiver';

        if (request()->has('fetch')) {
            if ($type == 'client') {
                return response()->json($this->business()->clients->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->nameLastFirst,
                        'email_sent_at' => $item->user->email_sent_at,
                        'onboard_status' => $item->onboard_status,
                    ];
                }));
            } else {
                return response()->json($this->business()->caregivers->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->nameLastFirst,
                        'email_sent_at' => $item->user->email_sent_at,
                        'onboard_status' => $item->onboarded ? 'Onboarded' : 'Not Onboarded',
                    ];
                }));
            }
        }

        return view('business.reports.onboard-status', compact('type'));
    }

    /**
     * Details on each attempted clock in and clock out
     *
     * @return Response
     */
    public function evv()
    {
        if (request()->expectsJson() && request()->input('json')) {
            $report = new EVVReport();
            $report->where('business_id', $this->business()->id);

            if ($method = request()->input('method')) {
                if ($method === 'geolocation') $report->geolocationOnly();
                if ($method === 'telephony') $report->telephonyOnly();
            }
            if (strlen(request()->input('verified'))) {
                $report->where('verified', request()->input('verified'));
            }
            $this->addShiftReportFilters($report, request());
            return $report->rows();
        }

        return view('business.reports.evv');
    }

    public function contacts()
    {
        $type = request()->type == 'client' ? 'client' : 'caregiver';

        if (request()->has('fetch')) {
            if ($type == 'client') {
                return response()->json($this->business()->clients->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->nameLastFirst,
                        'email' => $item->user->email,
                        'numbers' => $item->user->phoneNumbers,
                        'address' => $item->user->addresses()->where('type', 'evv')->first(),
                    ];
                }));
            } else {
                return response()->json($this->business()->caregivers->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->nameLastFirst,
                        'email' => $item->user->email,
                        'numbers' => $item->user->phoneNumbers,
                        'address' => $item->user->addresses()->where('type', 'home')->first(),
                    ];
                }));
            }
        }

        return view('business.reports.contacts', compact('type'));
    }

    /**
     * Show the page to generate a revenue report
     * 
     * @return Response
     */
    public function revenuePage()
    {
        return view('business.reports.revenue');
    }

    /**
     * Handle the request to generate a revenue report
     * @param Request $request
     * 
     * @return Response
     */
    public function revenueReport(Request $request) {
        $current = $this->organizeRevenueReport($request);
        $prior = [];

        if($request->compare_to_prior) {
            $difference = (new Carbon($request->start_date))->diffInDays((new Carbon($request->end_date)));
            $newStartDate = (new Carbon($request->start_date))->subDays($difference+1)->format('m/d/Y');
            $newEndDate = (new Carbon($request->end_date))->subDays(1)->format('m/d/Y');
            $newRequest = $request;
            $newRequest->merge([
                'start_date' => $newStartDate,
                'end_date' => $newEndDate,
            ]);

            $prior = $this->organizeRevenueReport($newRequest);
        }

        return json_encode(compact('current', 'prior'));
    }

    /**
     * Organize the shifts data into the required format for a full financial revenue report
     * @param Request $request 
     * 
     * @return array
     */    
    private function organizeRevenueReport(Request $request) {
        $report = new ShiftsReport();
        $report->where('business_id', $this->business()->id)->orderBy('checked_in_time');
        $this->addShiftReportFilters($report, $request);
        $data = $report->rows();
        $groupedByDate = [];

        foreach ($data as $i => $shiftReport) {
            $date = (new Carbon($shiftReport['checked_in_time']))->format('m/d/Y');

            if(isset($groupedByDate[$date])) {
                $groupedByDate[$date][] = $shiftReport;
            }else {
                $groupedByDate[$date] = [$shiftReport];
            }
        }

        /* Add days with no shift worked
        $numberOfDays = (new Carbon($request->start_date))->diffInDays((new Carbon($request->end_date)));
        for ($i=0; $i < $numberOfDays; $i++) { 
            $date = (new Carbon($request->start_date))->addDays($i+1);
            $formattedDate = $date->format('m/d/Y');
            if($formattedDate == '08/10/2018') {
                echo 'i:'.$i;
            }
            if($date->diffInDays((new Carbon($request->end_date))) < 0) {
                break;
            }
            
            if(!isset($groupedByDate[$formattedDate])) {
                $groupedByDate[$formattedDate] = [];
            }
        }*/

        foreach ($groupedByDate as $date => $itemsArray) {
            $total = [
                'revenue' => 0.0,
                'wages' => 0.0,
                'profit' => 0.0,
            ];

            foreach ($itemsArray as $entry) {
                $total['revenue'] += (float) $entry['provider_total'] + (float) $entry['caregiver_total'];
                $total['wages'] += (float) $entry['caregiver_total'];
                $total['profit'] += (float) $entry['provider_total'];
            }
            
            $groupedByDate[$date] = $total;
        }

        return $groupedByDate;
    }
}
