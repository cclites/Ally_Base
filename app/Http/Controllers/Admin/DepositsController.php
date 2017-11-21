<?php
namespace App\Http\Controllers\Admin;

use App\Business;
use App\Deposit;
use App\Http\Controllers\Controller;
use App\Payments\DepositProcessor;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DepositsController extends Controller
{
    public function index()
    {
        return view('admin.deposits.index');
    }

    public function pendingIndex()
    {
        return view('admin.deposits.pending');
    }

    public function report(Request $request, Business $business)
    {
        $startDate = new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York');
        $endDate = new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York');

        // Make UTC to match DB
        $startDate->setTimezone('UTC');
        $endDate->setTimezone('UTC');

        $deposits = Deposit::with(['transaction', 'caregiver', 'business'])
            ->where('business_id', $business->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'DESC')
            ->get();
        return $deposits;
    }

    public function pendingDeposits(Request $request, Business $business)
    {
        $startDate = new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York');
        $endDate = new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York');

        $processor = new DepositProcessor($business, $startDate, $endDate, logger());
        return $processor->getDepositData();
    }

    public function deposit(Request $request, Business $business)
    {
        $startDate = new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York');
        $endDate = new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York');

        $processor = new DepositProcessor($business, $startDate, $endDate, logger());
        $count = $processor->process();
        return new SuccessResponse('There were ' . $count . ' successful transactions.');
    }

}