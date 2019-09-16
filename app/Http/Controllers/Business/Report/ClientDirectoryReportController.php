<?php
namespace App\Http\Controllers\Business\Report;

use App\CustomField;
use App\Http\Controllers\Business\BaseController;
use App\Reports\ClientDirectoryReport;
use Illuminate\Http\Request;

class ClientDirectoryReportController extends BaseController
{
    /**
     * Get the Client directory.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $fields = CustomField::forAuthorizedChain()
            ->where('user_type', 'client')
            ->with('options')
            ->get();

        if ($request->filled('json')) {
            $page = $request->input('page', 1);
            $sortBy = $request->input('sort', 'lastname');
            $sortOrder = $request->input('desc', false) == 'true' ? 'desc' : 'asc';

            $report = new ClientDirectoryReport();
            $report->forRequestedBusinesses()
                ->setCustomFields($fields)
                ->setClientTypeFilter($request->client_type)
                ->setStatusAliasFilter($request->status_alias_id)
                ->setActiveFilter($request->active)
                ->setPageCount(50)
                ->setCurrentPage($page)
                ->setSort($sortBy, $sortOrder)
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

        return view('business.reports.client_directory', compact('fields'));
    }
}