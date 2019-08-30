<?php
namespace App\Http\Controllers\Business\Report;

use App\CustomField;
use App\Http\Controllers\Business\BaseController;
use App\Reports\CaregiverDirectoryReport;
use Illuminate\Http\Request;

class CaregiverDirectoryReportController extends BaseController
{
    /**
     * Shows the page to generate the caregiver directory
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $fields = CustomField::forAuthorizedChain()
            ->where('user_type', 'caregiver')
            ->with('options')
            ->get();

        if ($request->filled('json')) {
            $report = new CaregiverDirectoryReport();
            $report->query()->leftJoin('users', 'caregivers.id', '=', 'users.id');
            $report->forRequestedBusinesses()
                ->setCustomFields($fields)
                ->setActiveFilter($request->active)
                ->setStatusAliasFilter($request->status_alias_id)
                ->setCurrentPage($request->current_page)
                ->setPageCount(100)
                ->setForExport($request->export == '1');

            if ($request->export == '1') {
                return $report->setDateFormat('m/d/Y g:i A', 'America/New_York')
                    ->download();
            }

            // rows() has to be called for the private variable total_count to be set within the report
            $rows = $report->rows();
            $total = $report->getTotalCount();

            return response()->json(['rows' => $rows, 'total' => $total]);
        }

        return view('business.reports.caregiver_directory', compact('fields'));
    }
}