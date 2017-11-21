<?php
namespace App\Http\Controllers\Admin;

use App\Business;
use App\Http\Controllers\Controller;
use App\Payments\DepositProcessor;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DepositsController extends Controller
{
    public function index()
    {
        return view('admin.deposits.pending');
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