<?php

namespace App\Http\Controllers\Business\Report;

use App\Http\Controllers\Business\BaseController;
use App\Reports\SalespersonCommissionReport;
use Illuminate\Http\Request;
use App\SalesPerson;
use Illuminate\Support\Facades\Auth;

class SalespersonCommissionReportController extends BaseController
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {

        return view_component('sales-people-commission-report', 'Salesperson Commission Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }

    /**
     * Populate the salesperson dropdown
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function salesPeopleDropdown()
    {
        return SalesPerson::forBusinesses(Auth::user()->getBusinessIds())
            ->ordered()
            ->get()
            ->map(function ($person) {
                return [
                    'value' => $person->id,
                    'text' => $person->fullName(),
                ];
            });
    }

    /**
     * generate the report
     *
     * @param Request $request
     * @param SalespersonCommissionReport $report
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request, SalespersonCommissionReport $report)
    {

        if ($request->has('json')) {
            // Validate the user has access to the requested business
            $businesses = Auth::user()->filterAttachedBusinesses([$request->business]);
            if (empty($businesses)) {
                // If empty, show all office locations they are assigned to
                $businesses = Auth::user()->getBusinessIds();
            }

            $data = $report->forBusinesses($businesses)
                ->forDates($request->dates['start'], $request->dates['end'], Auth::user()->officeUser->getTimezone())
                ->forSalespersonId($request->salesperson)
                ->rows();

            return response()->json($data);
        }
    }
}