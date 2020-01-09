<?php

namespace App\Http\Controllers\Business;

use App\Billing\Service;
use App\Http\Requests\TimesheetReportRequest;
use App\ReferralSource;
use App\Reports\PayrollReport;
use App\Shifts\ShiftStatusManager;
use Auth;
use App\Billing\Payments\Methods\BankAccount;
use App\Business;
use App\Caregiver;
use App\Prospect;
use App\Client;
use App\EmergencyContact;
use App\Billing\Payments\Methods\CreditCard;
use App\Billing\Deposit;
use App\Billing\GatewayTransaction;
use App\Billing\Payment;
use App\Reports\CaregiverPaymentsReport;
use App\Reports\ClientCaregiversReport;
use App\Reports\ClientChargesReport;
use App\Reports\ProviderReconciliationReport;
use App\Reports\ScheduledPaymentsReport;
use App\Reports\ScheduledVsActualReport;
use App\Reports\ShiftsReport;
use App\Reports\ClientDirectoryReport;
use App\Reports\CaregiverDirectoryReport;
use App\Reports\ProspectDirectoryReport;
use App\Responses\ErrorResponse;
use App\Schedule;
use App\Scheduling\ScheduleAggregator;
use App\Shift;
use App\Shifts\AllyFeeCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use App\Reports\EVVReport;
use App\CustomField;
use App\OfficeUser;
use Illuminate\Support\Facades\Gate;
use Twilio\Rest\Taskrouter\V1\Workspace\TaskQueue\TaskQueuesStatisticsInstance;

class ReportsController extends BaseController
{

    public function index()
    {
        if( !Gate::allows( 'view-reports' ) ) abort( 403);

        $data['type'] = $this->business()->type;
        $data['role'] = ['role_type' => Auth::user()->role_type];

        return view('business.reports.index', ['data' => json_encode($data)]);
    }

    public function averyLabels()
    {
        return view( 'business.reports.avery_report' );
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

        $report->forRequestedBusinesses()
            ->query()
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


    public function reconciliation(Request $request, ProviderReconciliationReport $report)
    {
        if ($request->expectsJson() && $request->input('json')) {
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $report->between(Carbon::parse($request->start_date), Carbon::parse($request->end_date . ' 23:59:59'));
            }

            return $report->forRequestedBusinesses()
                ->forTypes($request->input('types'))
                ->orderBy('created_at', 'DESC')
                ->rows();
        }

        if ($request->input('export')) {
            return $report->forRequestedBusinesses()
                ->orderBy('created_at', 'DESC')
                ->setDateFormat('m/d/Y g:i A', $this->business()->timezone)
                ->download();
        }

        return view('business.reports.reconciliation');
    }

    public function deposits()
    {
        $deposits = Deposit::forRequestedBusinesses()
            ->where('deposit_type', 'business')
            ->orderBy('created_at', 'DESC')
            ->get();
        return view('business.reports.deposits', compact('deposits'));
    }

    public function payments()
    {
        $payments = Payment::forRequestedBusinesses()
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
        $report->forRequestedBusinesses();
        $scheduled = (clone $report)->rows()->sum('business_allotment');
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

        $caregivers = Caregiver::forRequestedBusinesses()
            ->select('caregivers.id')
            ->get()
            ->sortBy('nameLastFirst')
            ->values()
            ->all();
        $clients = Client::forRequestedBusinesses()
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

    public function shift(Request $request, $id)
    {
        $report = new ShiftsReport();
        $report->where('id', $id);
        return $report->rows()->first();
    }

    public function caregiverPayments(Request $request)
    {
        $report = new CaregiverPaymentsReport();
        $this->addShiftReportFilters($report, $request);

        return $report->rows();
    }

    public function clientCharges(Request $request)
    {
        $report = new ClientChargesReport();
        $this->addShiftReportFilters($report, $request);

        return $report->rows();
    }

    public function clientCaregivers(Request $request)
    {
        if ($request->expectsJson()) {
            $report = new ClientCaregiversReport();
            return $report->forRequestedBusinesses()->rows();
        }

        return view('business.reports.client-caregiver-rates');
    }

    /**
     * Get a list of all clients that are missing an email address
     */
    public function clientEmailMissing()
    {
        $clients = Client::forRequestedBusinesses()
            ->whereEmail('%@noemail.allyms.com')
            ->ordered();

        return view('business.reports.client_email_missing', compact('clients'));
    }

    public function creditCardExpiration()
    {
        return view('business.reports.credit-card-expiration', compact('cards'));
    }

    public function creditCards()
    {
        $report_date = Carbon::now()->addDays(request('daysFromNow'));

        $defaultCardsIds = Client::forRequestedBusinesses()->where('default_payment_type', CreditCard::class)->pluck('default_payment_id')->toArray();
        $backupCardIds = Client::forRequestedBusinesses()->where('backup_payment_type', CreditCard::class)->pluck('backup_payment_id')->toArray();

        $cards = CreditCard::with('user')
            ->whereIn('id', array_merge($defaultCardsIds, $backupCardIds))
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
        // added a simple redirect as per ALLY-1394 which asked to remove this report. The front-end link is removed, so any sort of
        // manual navigation to this route will also be rebuffed with the below line
        return back();

        $caregivers = Caregiver::forRequestedBusinesses()
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
        // added a simple redirect as per ALLY-1394 which asked to remove this report. The front-end link is removed, so any sort of
        // manual navigation to this route will also be rebuffed with the below line
        return back();

        $clients = Client::forRequestedBusinesses()
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

    protected function addShiftReportFilters(ShiftsReport $report, Request $request)
    {
        // Restrict businesses
        $report->forRequestedBusinesses();

        $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date']);
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

        if ($request->filled('client_type')) {
            $report->query()->whereHas('client', function($query) use ($request) {
                $query->where('client_type', $request->client_type);
            });
        }

        if ($request->filled('service_code')) {
            $report->query()->whereHas('services', function ($query) use ($request) {
                $query->where('id', $request->service_code);
            });
        }

        if ($status = $request->input('status')) {
            if ($status === 'charged') {
                $report->query()->whereReadOnly();
            } elseif ($status === 'uncharged') {
                $report->query()->wherePending();
            }
        }

        if ($confirmed = $request->input('confirmed')) {
            if ($confirmed === 'unconfirmed') {
                $report->query()->where('status', Shift::WAITING_FOR_CONFIRMATION);
            }
            else {
                $report->query()->whereNotIn('status',  [Shift::WAITING_FOR_CONFIRMATION, Shift::CLOCKED_IN]);
            }
        }

        $flagType = $request->input('flag_type');
        if ($flagType && $flagType !== 'any') {
            if ($flagType === 'none') {
                $report->query()->doesntHave('shiftFlags');
            }
            else if (is_array($flags = $request->input('flags'))) {
                $report->query()->whereFlagsIn($flags);
            }
        }
    }

    public function exportTimesheets()
    {
        $caregivers = Caregiver::forRequestedBusinesses()->ordered()->get();
        $clients = Client::forRequestedBusinesses()->ordered()->get();
        return view('business.reports.export_timesheets', compact('caregivers', 'clients'));
    }

    public function timesheetData(TimesheetReportRequest $request)
    {
        $data = $request->validated();
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];
        $business = $request->getBusiness();
        $timezone = $business->timezone;
        $override_ally_logo = $business->logo;

        $client_shift_groups = $this->clientShiftGroups($business, $data);
        $viewData = compact('client_shift_groups', 'start_date', 'end_date', 'timezone', 'override_ally_logo');

        switch ($data['export_type']) {
            case 'pdf':
                $pdf = PDF::loadView('business.reports.print.timesheets', $viewData);
                return $pdf->download('timesheet_export.pdf');
            default:
                $viewData['render'] = 'html';
                return view('business.reports.print.timesheets', $viewData);
        }
    }

    public function printPaymentHistory($caregiver_id, $year)
    {
        Carbon::setWeekStartsAt(Carbon::MONDAY);

        $caregiver = Caregiver::find($caregiver_id);
        $this->authorize('read', $caregiver);

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

        // TODO: We should not rely on a single business here  (ALLY-431)
        $business = $caregiver->businesses->first();

        $pdf = PDF::loadView('caregivers.reports.print_payment_history', compact('caregiver', 'deposits', 'business'));
        return $pdf->download($year . '_year_summary.pdf');
    }

    public function printPaymentDetails($id, $caregiver_id)
    {
        $deposit = Deposit::find($id);
        $this->authorize('read', $deposit);

        $caregiver = Caregiver::find($caregiver_id);
        $this->authorize('read', $caregiver);

        $shifts = $this->getPaymentShifts($id, $caregiver_id);

        // TODO: We should not rely on a single business here  (ALLY-431)
        $business = $caregiver->businesses->first();

        if (strtolower(request()->type) == 'pdf') {
            $pdf = PDF::loadView('caregivers.print.payment_details', compact('business', 'shifts', 'deposit'))->setOrientation('landscape');
            return $pdf->download('deposit_details.pdf');
        }

        $render = 'html';
        return view('caregivers.print.payment_details', compact('business', 'shifts', 'deposit', 'render'));
    }

    /**
     * @param $id
     * @param $caregiverId
     * @return mixed
     */
    protected function getPaymentShifts($id, $caregiver_id)
    {
        $shifts = Shift::with('deposits', 'activities')
            ->forRequestedBusinesses()
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

    private function clientShiftGroups(Business $business, array $data)
    {
        return $business->shifts()
            ->with('activities', 'client', 'caregiver')
            ->whereBetween('checked_in_time', [Carbon::parse($data['start_date']), Carbon::parse($data['end_date'])])
            ->when(isset($data['client_id']) && $data['client_id'], function ($query) use ($data) {
                return $query->where('client_id', $data['client_id']);
            })
            ->when(isset($data['caregiver_id']) && $data['caregiver_id'], function ($query) use ($data) {
                return $query->where('caregiver_id', $data['caregiver_id']);
            })
            ->when(isset($data['client_type']) && $data['client_type'], function ($query) use ($data) {
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
     * Client Services Coordinators Report
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function servicesCoordinator(Request $request)
    {
        if ($request->wantsJson() && filled($request->input('json'))) {
            $data = Client::forRequestedBusinesses()
                ->with(['servicesCoordinator', 'business'])
                ->with(['notes' => function($query) {
                    return $query->where('type', 'phone')
                        ->latest();
                }])
                ->whereHas('servicesCoordinator')
                ->when($request->services_coordinator_id, function ($q, $value) {
                    $q->where('services_coordinator_id', $value);
                })
                ->when($request->client_id, function ($q, $value) {
                    $q->where('id', $value);
                })
                ->when($request->client_status, function ($q, $value) {
                    $q->whereHas('user', function ($q) use ($value) {
                        $q->where('active', $value);
                    });
                })
                ->get()
                ->map(function (\App\Client $client) {
                    $lastClientNote = $client->notes->first();
                    return [
                        'office_location' => $client->business->name,
                        'services_coordinator' => $client->servicesCoordinator->nameLastFirst,
                        'client_id' => $client->id,
                        'client_name' => $client->nameLastFirst,
                        'profile_url' => $client->getProfileUrl(),
                        'client_status' => $client->active,
                        'days_since_contact' => $lastClientNote ? Carbon::now()->diffInDays($lastClientNote->created_at) : '-',
                    ];
                })
                ->filter(function ($data) use ($request) {
                    if (filled($request->days_since_contact) && is_numeric($request->days_since_contact)) {
                        return $data['days_since_contact'] != '-' &&
                            intval($data['days_since_contact']) <= intval($request->days_since_contact);
                    }

                    return true;
                })
                ->values();

            return response()->json($data);
        }

        $servicesCoordinators = OfficeUser::forRequestedBusinesses()
            ->whereHas('assignedClients')
            ->with('user')
            ->get();

        return view('business.reports.services_coordinator', compact('servicesCoordinators'));
    }

    /**
     * Display a listing of the caregiver's working anniversaries
     *
     * @return \Illuminate\Http\Response
     */
    public function caregiverAnniversary( Request $request )
    {
        if( $request->filled( 'json' ) ){

            return Caregiver::forRequestedBusinesses()->get()->map(function ($item) {
                return array_merge($item->toArray(), ['created_at' => $item->created_at->toDateTimeString()]);
            });
        }

        return view( 'business.reports.caregiver_anniversary' );
    }

    /**
     * Display a listing of the users and their birthdays.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function userBirthday(Request $request)
    {
        $type = $request->type == 'clients' ? 'clients' : 'caregivers';
        $type = ucfirst($type);
        return view('business.reports.user_birthday', compact('type'));
    }

    public function userBirthdayData(Request $request)
    {
        $type = strtolower($request->type) == 'clients' ? 'clients' : 'caregivers';

        if($type == 'clients') {
            return Client::forRequestedBusinesses()->get();
        }

        return Caregiver::forRequestedBusinesses()->get();
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
     * Shows the page to generate the prospect directory
     *
     * @return Response
     */
    public function prospectDirectory()
    {
        $prospects = Prospect::forRequestedBusinesses()->get();
        return view('business.reports.prospect_directory', compact('prospects'));
    }

    /**
     * Handle the request to generate the prospect directory
     *
     * @param \Illuminate\Http\Request $request
     * @return Response
     */
    public function generateProspectDirectoryReport(Request $request)
    {
        $report = new ProspectDirectoryReport();
        $report->forRequestedBusinesses();

        if($request->start_date && $request->end_date) {
            $report->where('created_at','>', (new Carbon($request->start_date))->format('Y-m-d'));
            $report->where('created_at','<', (new Carbon($request->end_date))->format('Y-m-d'));
        }

        $report->applyColumnFilters($request->except(['start_date','end_date']));

        if ($report->count() > 1000) {
            // Limit to 1K prospects for performance reasons
            return new ErrorResponse(400, 'There are too many prospects to report.  Please reduce your date range.');
        }

        if ($request->has('export') && $request->export == true) {
            return $report->download();
        }

        return $report->rows();
    }


    /**
     * See how many shifts have been worked by a caregiver
     *
     * @return Response
     */
    public function caregiverShifts()
    {
        if (request()->has('fetch')) {
            $report = Shift::forRequestedBusinesses()
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
        $users = Caregiver::forRequestedBusinesses()->ordered()->get();

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
            $report = Shift::forRequestedBusinesses()
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
        $users = Client::forRequestedBusinesses()->active()->ordered()->get();

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
                $query = Client::forRequestedBusinesses();
                return response()->json($query->get()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->nameLastFirst,
                        'email_sent_at' => $item->user->welcome_email_sent_at,
                        'onboard_status' => $item->agreement_status,
                    ];
                }));
            } else {
                $query = Caregiver::forRequestedBusinesses();
                return response()->json($query->get()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->nameLastFirst,
                        'email_sent_at' => $item->user->welcome_email_sent_at,
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
     * @param \App\Reports\EVVReport $report
     * @return Response
     */
    public function evv(EVVReport $report)
    {
        if ( request()->expectsJson() && request()->input( 'json' ) ) {

            $report->forRequestedBusinesses();

            if ( $method = request()->input('method') ) {
                if ($method === 'geolocation') $report->geolocationOnly();
                if ($method === 'telephony') $report->telephonyOnly();
            }
            if (strlen(request()->input('verified'))) {
                $report->where('verified', request()->input('verified'));
            }
            $this->addShiftReportFilters( $report, request() );

            $rows = $report->rows();

            if( request()->input( 'summarize', false ) === '1' ){

                // process the rows data as a summary
                $summary[ 'client' ] = $rows->groupBy( 'client_id' )->map( function( $client ){

                    $data[ 'clientId'              ] = $client->first()->client->id;
                    $data[ 'clientName'            ] = $client->first()->client->name;
                    $data[ 'totalShifts'           ] = $client->count();
                    $data[ 'totalVerifiedShifts'   ] = $client->whereStrict( 'verified', 1 )->count();
                    $data[ 'totalUnverifiedShifts' ] = $client->whereStrict( 'verified', 0 )->count();

                    $data[ 'verifiedPercentage'    ] = $data[ 'totalVerifiedShifts' ] / $data[ 'totalShifts' ];

                    $data[ 'totalBlocked'          ] = $client->whereStrict( 'checked_in_distance', 0 )->count();
                    $data[ 'totalOutsideRange'     ] = $client->whereStrict( 'checked_in_distance', '>', \App\Shifts\ClockIn::MAXIMUM_DISTANCE_METERS )->count();

                    return $data;
                });

                $summary[ 'caregiver' ] = $rows->groupBy( 'caregiver_id' )->map( function( $caregiver ){

                    $data[ 'caregiverId'           ] = $caregiver->first()->caregiver->id;
                    $data[ 'caregiverName'         ] = $caregiver->first()->caregiver->name;
                    $data[ 'totalShifts'           ] = $caregiver->count();
                    $data[ 'totalVerifiedShifts'   ] = $caregiver->whereStrict( 'verified', 1 )->count();
                    $data[ 'totalUnverifiedShifts' ] = $caregiver->whereStrict( 'verified', 0 )->count();

                    $data[ 'verifiedPercentage'    ] = $data[ 'totalVerifiedShifts' ] / $data[ 'totalShifts' ];

                    $data[ 'totalBlocked'          ] = $caregiver->whereStrict( 'checked_in_distance', 0 )->count();
                    $data[ 'totalOutsideRange'     ] = $caregiver->whereStrict( 'checked_in_distance', '>', \App\Shifts\ClockIn::MAXIMUM_DISTANCE_METERS )->count();

                    return $data;
                });

                return response()->json( $summary );
            }

            return $rows;
        }

        return view('business.reports.evv');
    }

    public function contacts()
    {
        $type = request()->type == 'client' ? 'client' : 'caregiver';

        if (request()->has('fetch')) {
            if ($type == 'client') {
                $query = Client::forRequestedBusinesses();
                return response()->json($query->get()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->nameLastFirst,
                        'email' => $item->user->email,
                        'numbers' => $item->user->phoneNumbers,
                        'address' => $item->user->addresses()->where('type', 'evv')->first(),
                    ];
                }));
            } else {
                $query = Caregiver::forRequestedBusinesses();
                return response()->json($query->get()->map(function ($item) {
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
     * Agency Payroll Report
     *
     * @param Request $request
     * @param PayrollReport $report
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function payrollReport(Request $request, PayrollReport $report)
    {
        if ($request->has('json') || $request->has('export')) {
            $data = $report->forRequestedBusinesses()
                ->forDates($request->start, $request->end)
                ->forCaregiver($request->caregiver)
                ->rows();

            if ($request->has('export')) {
                return $report->setDateFormat('m/d/Y g:i A', $this->business()->timezone)
                    ->download();
            }

            return response()->json($data);
        }

        $caregivers = Caregiver::forRequestedBusinesses()->active()->ordered()->get();

        return view('business.reports.payroll', compact('caregivers'));
    }

    /**
     * Show the page to generate a revenue report
     *
     * @return Response
     */
    public function revenuePage()
    {
        $clients = Client::forRequestedBusinesses()->select('id')->get()
            ->sortBy('name')->values()->all();
        $clients = collect($clients);
        $caregivers = Caregiver::forRequestedBusinesses()->select('id')->get()
            ->sortBy('name')->values()->all();
        $caregivers = collect($caregivers);
        $clientTypes = Client::forRequestedBusinesses()
            ->select('client_type')
            ->distinct()
            ->pluck('client_type')
            ->map(function($item) {
                return [
                    'name' => title_case(str_replace('_', ' ', $item)),
                    'id' => $item
                ];
            });
        $serviceCodes = $this->businessChain()->services()->get();
        return view('business.reports.revenue', compact('clients', 'caregivers', 'clientTypes', 'serviceCodes'));
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
     * Show the page to generate a sales pipeline report
     *
     * @return Response
     */
    public function showSalesPipeline(Request $request)
    {
        if ($request->expectsJson()) {
            return $this->salesPipelineReport($request);
        }

        return view('business.reports.sales-pipeline');
    }

    /**
     * Handle the request to generate a report for the sales pipeline
     * @param Request $request
     *
     * @return array
     */
    protected function salesPipelineReport(Request $request) {

        $this->validate($request, [
            'start_date' => 'required|string|date',
            'end_date' => 'required|string|date',
        ]);

        $startDate = new Carbon($request->start_date);
        $endDate = new Carbon($request->end_date);
        if($startDate->diffInMonths($endDate) > 6) {
            return new ErrorResponse(422, 'The selected date range cannot be more than 6 months.');
        }

        $prospects = Prospect::select([
                'id',
                'business_id', 
                'firstname',
                'lastname',
                'closed_loss',
                'closed_win', 
                'referred_by',
                'referral_source_id',
                'had_assessment_scheduled',
                'had_assessment_performed',
                'needs_contract',
                'expecting_client_signature',
                'needs_payment_info',
                'ready_to_schedule',
                'created_at',
            ])
            ->forRequestedBusinesses()
            ->whereBetween('created_at', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with('referralSource')
            ->get();

            return $prospects;
    }

    /**
     * Organize the shifts data into the required format for a full financial revenue report
     * @param Request $request
     *
     * @return array
     */
    private function organizeRevenueReport(Request $request)
    {
        $report = new ShiftsReport();
        $report->orderBy('checked_in_time');
        $this->addShiftReportFilters($report, $request);
        $data = $report->rows();
        $groupedByDate = [];

        foreach ($data as $i => $shiftReport) {
            // grouped by week
            $date = (new Carbon($shiftReport['checked_in_time']))->startOfWeek()->format('m/d/Y');

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
