<?php

namespace App\Http\Controllers\Business\Clients;

use App\Reports\ClientPaymentHistoryReport;
use App\Http\Controllers\Controller;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Client;

class ClientPaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param Client $client
     * @param ClientPaymentHistoryReport $report
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, Client $client, ClientPaymentHistoryReport $report)
    {
        $this->authorize('read', $client);

        $data = $report->applyFilters($client, $request->year)
            ->rows();

        return response()->json($data);
    }

    /**
     * Print the client yearly payment summary.
     *
     * @param Request $request
     * @param Client $client
     * @param ClientPaymentHistoryReport $report
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function print(Request $request, Client $client, ClientPaymentHistoryReport $report)
    {
        $this->authorize('read', $client);

        if (empty($request->year)) {
            $request->year = (int) Carbon::now()->format('Y');
        }

        $data = $report->applyFilters($client, $request->year)
            ->rows();

        $year = $request->year;
        $total = $data['total'];
        $payments = $data['rows'];
        $pdf = PDF::loadView('clients.print.yearly-payment-summary', compact('client', 'year', 'payments', 'total'));
        return $pdf->download(strtolower(str_slug($client->name . ' ' . $request->year . ' Payment Summary')) . '.pdf');
    }
}
