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
    protected $validCurrentStatuses = [Shift::WAITING_FOR_AUTHORIZATION, Shift::WAITING_FOR_INVOICE];

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
        if (!$shift) {
            return $this->massUpdate($request);
        }
        if (!in_array($shift->status, $this->validCurrentStatuses)) {
            return new ErrorResponse(400, 'Shift is not pending and therefore cannot be updated.');
        }
        if ($request->input('authorized')) {
            $shift->statusManager()->ackAuthorization();
        }
        else {
            $shift->statusManager()->unauthorize();
        }
        return new SuccessResponse('The shift has been updated.');
    }

    protected function massUpdate(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'start_date' => 'date',
            'end_date' => 'date',
        ], [
            'business_id.*' => 'A valid provider is required',
        ]);

        $startDate = (new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York'))->setTimezone('UTC');
        $endDate = (new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York'))->setTimezone('UTC');

        $query = Shift::whereBetween('checked_in_time', [$startDate, $endDate])
            ->whereIn('status', $this->validCurrentStatuses);
        if ($request->input('business_id')) $query->where('business_id', $request->input('business_id'));
        $shifts = $query->get();
        foreach($shifts as $shift) {
            if ($request->input('authorized')) {
                $shift->statusManager()->ackAuthorization();
            }
            else {
                $shift->statusManager()->unauthorize();
            }
        }
        return new SuccessResponse('The shifts have been updated.');
    }
}