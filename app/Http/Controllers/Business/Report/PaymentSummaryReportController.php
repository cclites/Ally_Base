<?php

namespace App\Http\Controllers\Business\Report;

use App\Business;
use App\Billing\Payments\PaymentMethodType;
use App\Client;
use App\ClientType;
use App\Http\Controllers\Business\BaseController;
use App\Http\Requests\PaymentSummaryReportRequest;
use App\Reports\PaymentSummaryReport;
use Illuminate\Http\Response;

class PaymentSummaryReportController extends BaseController
{
    /**
     * Get the payment summary by payment method report.
     *
     * @param PaymentSummaryReportRequest $request
     * @param PaymentSummaryReport $report
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View|void
     */
    public function index(PaymentSummaryReportRequest $request, PaymentSummaryReport $report)
    {
        if ($request->wantsReportData()) {
            $report->query()->forRequestedBusinesses();

            $report->applyFilters(
                $request->filterDateRange(),
                $request->client_type,
                $request->client,
                $request->payment_method
            );

            if ($request->forExport()) {
                return $report->setDateFormat('m/d/Y g:i A', auth()->user()->getTimezone())
                    ->download();
            }
            $report->rows();
            \Log::info(\DB::getQueryLog());

            return response()->json([
                'results' => $report->rows(),
                'totals' => $report->totals(),
            ]);
        }

        return view_component('payment-summary-by-payer', 'Payment Summary by Payment Method', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }
}