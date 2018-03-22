<?php

namespace App\Http\Controllers\Admin;

use App\Business;
use App\Caregiver;
use App\Http\Controllers\Controller;
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
        if ($request->expectsJson() && $request->input('json')) {
            $report = new PendingTransactionsReport();
            $rows = $report->rows();
            if ($business_id = $request->input('business_id')) {
                return $rows->where('business_id', $business_id)->values();
            }
            return $rows;
        }
        return view('admin.reports.pending_transactions');
    }

    public function unpaidShifts(Request $request) {
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
        return view('admin.reports.finances');
    }

    public function financesData()
    {
        $payments = Payment::all();
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
            $item['percentage'] = $item['total'] / $total;
            return $item;
        });
        return response()->json(compact('stats', 'breakdown'));
    }
}
