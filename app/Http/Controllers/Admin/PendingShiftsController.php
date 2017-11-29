<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Reports\ScheduledPaymentsReport;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PendingShiftsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $startDate = new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York');
            $endDate = new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York');

            $report = new ScheduledPaymentsReport();
            if ($request->has('business_id')) $report->where('business_id', $request->input('business_id'));
            $report->between($startDate, $endDate);
            return $report->rows();
        }
        return view('admin.charges.pending_shifts');
    }

    public function update(Request $request, Shift $shift)
    {
        $status = ($request->input('authorized')) ? Shift::WAITING_FOR_CHARGE : Shift::WAITING_FOR_AUTHORIZATION;
        $validCurrentStatuses = [Shift::WAITING_FOR_APPROVAL, Shift::WAITING_FOR_AUTHORIZATION, Shift::WAITING_FOR_CHARGE];
        if (!in_array($shift->status, $validCurrentStatuses)) {
            return new ErrorResponse(400, 'Shift is not pending and therefore cannot be updated.');
        }
        if ($shift->update(['status' => $status])) {
            return new SuccessResponse('The shift has been updated.');
        }
        return new ErrorResponse(500, 'Unable to update shift.');
    }
}