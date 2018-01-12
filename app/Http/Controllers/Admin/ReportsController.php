<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Reports\ShiftsReport;
use App\Reports\UnsettledReport;
use App\Shift;
use App\Shifts\ShiftStatusManager;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
            ->where(function(Builder $q){
                foreach (['client_id', 'caregiver_id', 'business_id'] as $param) {
                    if (request($param)) {
                        $q->orWhere($param, request($param));   
                    }
                }
            });
        
        return $report->rows();
    }
}
