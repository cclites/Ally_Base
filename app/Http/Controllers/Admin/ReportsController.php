<?php

namespace App\Http\Controllers\Admin;

use App\Business;
use App\Caregiver;
use App\Http\Controllers\Controller;
use App\Reports\DuplicateDepositReport;
use App\Reports\OnHoldReport;
use App\Reports\PendingTransactionsReport;
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
}
