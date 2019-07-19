<?php


namespace App\Http\Controllers\Business\Report;

use App\Billing\Payer;
use App\Client;
use App\Http\Controllers\Business\BaseController;
use Illuminate\Http\Request;
use App\Reports\PaymentSummaryByPayerReport;

use Log;

class PaymentSummaryByPayerReportController extends BaseController
{
    public function index(Request $request, PaymentSummaryByPayerReport $report){


        if ($request->filled('json')) {

            $timezone = auth()->user()->role->getTimezone();

            $report->setTimezone($timezone)
                ->applyFilters(
                    $request->start,
                    $request->end,
                    $request->business,
                    $request->client_type,
                    $request->client,
                    $request->payer
                );

            $data = $report->rows();

            return response($data);
        }

        return view_component(
            'payment-summary-by-payer',
            'Payment Summary By Payer Report',
             [],
            [
                'Home' => route('home'),
                'Reports' => route('business.reports.index')
            ]
        );
    }


}