<?php

namespace App\Http\Controllers\Admin;

use App\Billing\Generators\CaregiverInvoiceGenerator;
use App\Billing\Queries\CaregiverInvoiceQuery;
use Auth;
use App\Billing\Payments\Methods\BankAccount;
use App\Business;
use App\Caregiver;
use App\Client;
use App\Billing\Payments\Methods\CreditCard;
use App\Billing\GatewayTransaction;
use App\Http\Controllers\Controller;
use App\Reports\CaregiverPaymentsReport;
use App\Reports\ClientChargesReport;
use App\Reports\DuplicateDepositReport;
use App\Reports\EVVReport;
use App\Reports\OnHoldReport;
use App\Reports\PendingTransactionsReport;
use App\Billing\Payment;
use App\Reports\ShiftsReport;
use App\Reports\UnpaidShiftsReport;
use App\Reports\UnsettledReport;
use App\Shift;
use App\Shifts\ShiftStatusManager;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Reports\ActiveClientsReport;

class ReportsController extends Controller
{

    public function index()
    {
        $role = json_encode(['role_type' => Auth::user()->role_type]);
        return view('admin.reports.index', ['role' => $role]);
    }

    public function emails(Request $request, $type = null)
    {
        if (!$type) {
            return view('admin.reports.emails');
        }

        $query = User::where('email', 'NOT LIKE', '%@allyms.com')
            ->where('email', 'NOT LIKE', '%noemail%');

        if ($type === 'client_payments') {
            $request->validate(['date' => 'required|date']);
            $date = Carbon::parse($request->date, 'America/New_York');
            $query->join('payments', 'payments.client_id', '=', 'users.id')
                ->whereBetween('payments.created_at', [
                    $date->copy()->setTime(0,0,0)->setTimezone('UTC')->toDateTimeString(),
                    $date->copy()->setTime(23, 59, 59)->setTimezone('UTC')->toDateTimeString(),
                ]);
        }

        if ($type === 'caregiver_deposits') {
            $request->validate(['date' => 'required|date']);
            $date = Carbon::parse($request->date, 'America/New_York');
            $query->join('deposits', 'deposits.caregiver_id', '=', 'users.id')
                  ->whereBetween('deposits.created_at', [
                      $date->copy()->setTime(0,0,0)->setTimezone('UTC')->toDateTimeString(),
                      $date->copy()->setTime(23, 59, 59)->setTimezone('UTC')->toDateTimeString(),
                  ]);
        }

        return $query->pluck('email')->implode(',');
    }

    public function unsettled($data = 'data')
    {
        $statuses = ShiftStatusManager::getUnsettledStatuses();

        if ($data === 'statuses') {
            return response($statuses);
        }

        $statuses = request('status', $statuses);

        $startDate = new Carbon(request('start_date') . ' 00:00:00', 'America/New_York');
        $endDate = new Carbon(request('end_date') . ' 23:59:59', 'America/New_York');

        $report = new ShiftsReport;
        $report->between($startDate, $endDate)
            ->forRequestedBusinesses()
            ->query()
            ->whereIn('status', $statuses)
            ->where(function (Builder $q) {
                foreach (['client_id', 'caregiver_id', 'business_id'] as $param) {
                    if (request($param)) {
                        $q->orWhere($param, request($param));
                    }
                }
            });

        return $report->rows();
    }

    public function onHold(Request $request)
    {
        if ($request->expectsJson() && $request->input('json')) {
            $report = new OnHoldReport();
            $report->forBusiness($request->input('business_id'));
            return $report->rows();
        }
        return view('admin.reports.on_hold');
    }

    public function pendingTransactions(Request $request)
    {
        set_time_limit(0);
        if ($request->expectsJson() && $request->input('json')) {
            $report = new PendingTransactionsReport();
            $rows = $report->rows();
            if ($business_id = $request->input('business_id')) {
                return $rows->where('business_id', $business_id)->values();
            }
            return $rows
                ->map(function ($item) {
                    $item['deposit_outstanding'] = (int)$item['deposit_outstanding'];
                    $item['payment_outstanding'] = (int)$item['payment_outstanding'];
                    return $item;
                });
        }
        return view('admin.reports.pending_transactions');
    }

    public function unpaidShifts(Request $request)
    {
        if ($request->expectsJson() && $request->input('json')) {
            $report = new UnpaidShiftsReport();
            if ($business_id = $request->input('business_id')) {
                $report->where('business_id', $business_id);
            }
            if ($caregiver_id = $request->input('caregiver_id')) {
                $report->where('caregiver_id', $caregiver_id);
            }
            if ($client_id = $request->input('client_id')) {
                $report->where('client_id', $client_id);
            }
            return $report->rows();
        }
        return view('admin.reports.unpaid_shifts');
    }

    public function sharedShifts(Request $request)
    {
        if ($request->expectsJson() && $request->input('json')) {
            $report = new DuplicateDepositReport();
            if ($business_id = $request->input('business_id')) {
                $report->where('business_id', $business_id);
            }
            if ($caregiver_id = $request->input('caregiver_id')) {
                $report->where('caregiver_id', $caregiver_id);
            }
            return $report->rows();
        }
        return view('admin.reports.shared_shifts');
    }

    public function caregiversDepositsWithoutBankAccount(CaregiverInvoiceQuery $query, CaregiverInvoiceGenerator $invoiceGenerator)
    {
        // removed as per ALLY-1394, did not delete this, rather removed the front end link that will lead here,
        // and then added this 'return back()' to rebuff manual attempts to access this report
        return back();

        if (!$caregivers = \Cache::get('caregivers_missing_accounts')) {
            $caregivers = Caregiver::active()->with('businessChains')->doesntHave('bankAccount')->get();
            $caregivers = $caregivers->map(function(Caregiver $caregiver) use ($query, $invoiceGenerator) {
                $array = $caregiver->toArray();
                $array['has_amount_owed'] = $query->notPaidInFull()->forCaregiver($caregiver->id)->exists()
                    || count($invoiceGenerator->getInvoiceables($caregiver)) > 0;
                return $array;
            });

            \Cache::put('caregivers_missing_accounts', $caregivers, 60);
        }

        return view('admin.reports.caregivers.deposits_without_bank_account', compact('caregivers'));
    }

    public function finances()
    {
        $businesses = Business::all()->map(function ($item) {
            return [
                'name' => $item->name,
                'id' => $item->id
            ];
        });

        return view('admin.reports.finances', compact('businesses'));
    }

    public function financesData(Request $request)
    {
        $payments = Payment::when($request->filled('provider'), function ($query) use ($request) {
                $query->where('business_id', $request->provider);
            })
            ->when($request->filled('start_date'), function ($query) use ($request) {
                $query->where('created_at', '>=', Carbon::parse($request->start_date));
            })
            ->when($request->filled('end_date'), function ($query) use ($request) {
                $query->where('created_at', '<=', Carbon::parse($request->end_date));
            })
            ->get();
        $stats = collect([]);
        $types = $payments->groupBy('payment_type');
        foreach ($types as $key => $value) {
            $stats->push([
                'name' => $key,
                'total_charges' => $value->sum('amount'),
                'total' => $value->sum('amount'),
                'caregiver' => $value->sum('caregiver_allotment'),
                'business' => $value->sum('business_allotment'),
                'system' => $value->sum('system_allotment')
            ]);
        }
        return response()->json(compact('stats'));
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

    protected function addShiftReportFilters($report, Request $request)
    {
        if ($request->has('start_date') || $request->has('end_date')) {
            $startDate = new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York');
            $endDate = new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York');
            $report->between($startDate, $endDate);
        }

        if ($request->input('import_id')) {
            $report->where('import_id', $request->import_id);
        }

        if ($request->input('transaction_id')) {
            $report->forTransaction(GatewayTransaction::findOrFail($request->input('transaction_id')));
        }

        if ($request->has('payment_method')) {
            $method = null;
            switch ($request->input('payment_method')) {
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
        if ($client_type = $request->input('client_type')) {
            $report->whereHas('client', function ($query) use ($client_type) {
                $query->where('client_type', $client_type);
            });
        }
    }

    /**
     * Display all clients with the number of visits by caregivers during a given date range
     */
    public function clientCaregiverVisits()
    {
        $clients = Client::all()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->nameLastFirst
            ];
        });
        $caregivers = Caregiver::all()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->nameLastFirst
            ];
        });
        return view('admin.reports.client_caregiver_visits', compact('clients','caregivers'));
    }

    public function clientCaregiverVisitsData(Request $request)
    {
        if ($request->filled('startDate') && $request->filled('endDate')) {
            $range = [Carbon::parse($request->startDate), Carbon::parse($request->endDate)];
        } else {
            $range = [now()->subWeeks(4), now()];
        }

        $clients = Client::when($request->filled('clientId'), function ($query) use ($request) {
                $query->where('id', $request->clientId);
            })
            ->with(['shifts' => function ($query) use ($range, $request) {
                $query->whereBetween('checked_in_time', $range);
                $query->when($request->filled('caregiverId'), function ($query) use ($request) {
                    $query->where('caregiver_id', $request->caregiverId);
                });
            }, 'shifts.caregiver'])
            ->get()
            ->map(function ($item) {
                $item->caregiver_shifts = $item->shifts->groupBy('caregiver.name');
                return $item;
            });

        $table_data = [];
        foreach ($clients as $client) {
            foreach ($client->caregiver_shifts as $key => $value) {
                $table_data[] = [
                    'client' => $client->name,
                    'caregiver' => $key,
                    'shift_count' => count($value)
                ];
            }
        }

        $range = [$range[0]->format('m/d/Y'), $range[1]->format('m/d/Y')];
        return response()->json(compact('range', 'table_data'));
    }

    /**
     * Compare active clients activity over the span of a given date range.
     *
     * @return mixed
     */
    public function activeClients() 
    {
        if (request()->wantsJson()) {
            // set defaults
            $range = [now()->subWeeks(4), now()];
            $compareRange = [now()->subWeeks(4), now()];

            if (request()->filled('start_date') && request()->filled('end_date')) {
                $range = [Carbon::parse(request()->start_date), Carbon::parse(request()->end_date)];
            }

            if (request()->filled('start_date2') && request()->filled('end_date2')) {
                $compareRange = [Carbon::parse(request()->start_date2), Carbon::parse(request()->end_date2)];
            }

            $report = new ActiveClientsReport(request()->business_id, $range, $compareRange);

            return response()->json($report->rows());
        }

        return view('admin.reports.active_clients');
    }

    public function evv(Request $request)
    {
        if ($request->expectsJson() && $request->input('json')) {
            $report = new EVVReport();
            if ($businessId = $request->input('business_id')) {
                $report->where('business_id', $businessId);
            }
            if ($method = $request->input('method')) {
                if ($method === 'geolocation') $report->geolocationOnly();
                if ($method === 'telephony') $report->telephonyOnly();
            }
            if (strlen($request->input('verified'))) {
                $report->where('verified', $request->input('verified'));
            }
            $this->addShiftReportFilters($report, $request);
            return $report->rows();
        }

        return view('admin.reports.evv');
    }
}
