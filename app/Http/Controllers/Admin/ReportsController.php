<?php

namespace App\Http\Controllers\Admin;

use App\BankAccount;
use App\Business;
use App\Caregiver;
use App\Client;
use App\CreditCard;
use App\GatewayTransaction;
use App\Http\Controllers\Controller;
use App\Reports\CaregiverPaymentsReport;
use App\Reports\ClientChargesReport;
use App\Reports\DuplicateDepositReport;
use App\Reports\OnHoldReport;
use App\Reports\PendingTransactionsReport;
use App\Payment;
use App\Reports\ShiftsReport;
use App\Reports\UnpaidShiftsReport;
use App\Reports\UnsettledReport;
use App\Shift;
use App\Shifts\ShiftStatusManager;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Reports\ActiveClientsReport;

class ReportsController extends Controller
{
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
            $rows = $report->rows();
            if ($business_id = $request->input('business_id')) {
                return $rows->where('business_id', $business_id)->values();
            }
            return $rows;
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

    public function caregiversDepositsWithoutBankAccount()
    {
        $businesses = Business::with([
            'caregivers' => function ($query) {
                $query->whereHas('shifts', function ($query) {
                    $query->where('status', 'WAITING_FOR_PAYOUT');
                })
                    ->doesntHave('bankAccount');
            }
        ])
            ->whereHas('caregivers', function ($query) {
                $query->whereHas('shifts', function ($query) {
                    $query->where('status', 'WAITING_FOR_PAYOUT');
                })
                    ->doesntHave('bankAccount');
            })
            ->get();

        return view('admin.reports.caregivers.deposits_without_bank_account', compact('businesses'));
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
                'total' => $value->sum('amount'),
                'caregiver' => $value->sum('caregiver_allotment'),
                'business' => $value->sum('business_allotment'),
                'system' => $value->sum('system_allotment')
            ]);
        }

        $total = $payments->sum('amount');
        $breakdown = collect([
            [
                'name' => 'ach',
                'label' => 'ACH/ACH-P/Offline',
                'total' => $payments->reduce(function ($carry, $item) {
                    switch (strtolower($item->payment_type)) {
                        case 'ach':
                        case 'ach-p':
                        case 'manual':
                            return $carry + $item->amount;
                        default:
                            return $carry;
                    }
                })
            ],
            [
                'name' => 'cc',
                'label' => 'CC/AMEX',
                'total' => $payments->reduce(function ($carry, $item) {
                    switch (strtolower($item->payment_type)) {
                        case 'amex':
                        case 'cc':
                            return $carry + $item->amount;
                        default:
                            return $carry;
                    }
                })
            ],
            [
                'name' => 'medicaid',
                'label' => 'MedicAid',
                'total' => $payments->reduce(function ($carry, $item) {
                    switch (strtolower($item->payment_type)) {
                        case 'medicaid':
                            return $carry + $item->amount;
                        default:
                            return $carry;
                    }
                })
            ]
        ])
            ->map(function ($item) use ($total) {
                $item['percentage'] = 0;
                if ($total != 0) {
                    $item['percentage'] = $item['total'] / $total;
                }
                return $item;
            });
        return response()->json(compact('stats', 'breakdown'));
    }

    public function shifts(Request $request)
    {
        $report = new ShiftsReport();
        $report->orderBy('checked_in_time');

        $this->addShiftReportFilters($report, $request);

        if ($request->input('export')) {
            return $report->setDateFormat('m/d/Y g:i A', 'America/New_York')
                ->download();
        }

        return $report->rows();
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
        return view('business.reports.client_caregiver_visits', compact('clients','caregivers'));
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
     * @return \Illuminate\View\View
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
}
