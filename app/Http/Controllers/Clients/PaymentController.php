<?php

namespace App\Http\Controllers\Clients;

use App\Billing\Payment;
use App\Billing\Queries\PaymentQuery;
use App\Billing\View\Html\HtmlPaymentView;
use App\Billing\View\PaymentViewGenerator;
use App\Billing\View\Pdf\PdfPaymentView;
use App\Client;
use App\Reports\ClientPaymentHistoryReport;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends BaseController
{
    /**
     * @var \App\Billing\Queries\PaymentQuery
     */
    protected $paymentQuery;

    public function __construct(PaymentQuery $paymentQuery)
    {
        $this->paymentQuery = $paymentQuery;
    }

    /**
     * Get list of client's payments by year.
     *
     * @param Request $request
     * @param ClientPaymentHistoryReport $report
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request, ClientPaymentHistoryReport $report)
    {
        if ($request->wantsJson() && filled($request->input('json'))) {
            $data = $report->applyFilters($this->client(), $request->year)
                ->rows();

            return response()->json($data);
        }

        return view('clients.payment_history', ['client' => $this->client()]);
    }

    /**
     * Print or view the Payment statement.
     *
     * @param Payment $payment
     * @param string $view
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Payment $payment, string $view = "html")
    {
        $this->authorize('read', $payment);

        $strategy = new HtmlPaymentView();
        if (strtolower($view) === 'pdf') {
            $strategy = new PdfPaymentView('payment-' . $payment->id . '.pdf');
        }

        $viewGenerator = new PaymentViewGenerator($strategy);
        return $viewGenerator->generate($payment);
    }

    /**
     * Print the client yearly payment summary.
     *
     * @param Request $request
     * @param ClientPaymentHistoryReport $report
     * @return mixed
     */
    public function printSummary(Request $request, ClientPaymentHistoryReport $report)
    {
        if (empty($request->year)) {
            $request->year = (int) Carbon::now()->format('Y');
        }

        $data = $report->applyFilters($this->client(), $request->year)
            ->rows();

        $year = $request->year;
        $total = $data['total'];
        $payments = $data['rows'];
        $client = $this->client();
        $pdf = PDF::loadView('clients.print.yearly-payment-summary', compact('client', 'year', 'payments', 'total'));
        return $pdf->download(strtolower(Str::slug($client->name . ' ' . $request->year . ' Payment Summary')) . '.pdf');
    }
}