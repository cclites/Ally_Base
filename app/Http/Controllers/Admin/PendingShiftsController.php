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

    public function update(Request $request, Shift $shift = null)
    {
        $authorized = $request->input('authorized');
        $validCurrentStatuses = [Shift::WAITING_FOR_APPROVAL, Shift::WAITING_FOR_AUTHORIZATION, Shift::WAITING_FOR_CHARGE];
        if (!$shift && $request->has('start_date')) {
            $startDate = (new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York'))->setTimezone('UTC');
            $endDate = (new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York'))->setTimezone('UTC');

            $query = Shift::whereBetween('checked_in_time', [$startDate, $endDate])
                          ->whereIn('status', $validCurrentStatuses);
            if ($request->input('business_id')) $query->where('business_id', $request->input('business_id'));
            $shifts = $query->get();
            foreach($shifts as $shift) {
                if ($authorized) {
                    $shift->statusManager()->ackAuthorization();
                }
                else {
                    $shift->statusManager()->unauthorize();
                }
            }
            return new SuccessResponse('The shifts have been updated.');
        }
        if (!in_array($shift->status, $validCurrentStatuses)) {
            return new ErrorResponse(400, 'Shift is not pending and therefore cannot be updated.');
        }
        if ($authorized) {
            $shift->statusManager()->ackAuthorization();
        }
        else {
            $shift->statusManager()->unauthorize();
        }
        return new SuccessResponse('The shift has been updated.');
    }
}