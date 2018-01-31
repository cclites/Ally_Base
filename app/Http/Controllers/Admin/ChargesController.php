<?php

namespace App\Http\Controllers\Admin;

use App\Business;
use App\Client;
use App\Payment;
use App\Payments\ClientPaymentAggregator;
use App\Payments\PaymentProcessor;
use App\Payments\PendingPayments;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChargesController extends Controller
{
    public function index(Request $request)
    {
        if ($request->expectsJson() && $request->input('json')) {
            $startDate = new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York');
            $endDate = new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York');

            // Make UTC to match DB
            $startDate->setTimezone('UTC');
            $endDate->setTimezone('UTC');

            $query = Payment::with(['transaction', 'client', 'business'])
                            ->whereBetween('created_at', [$startDate, $endDate])
                            ->orderBy('created_at', 'DESC');

            if ($business_id = $request->input('business_id')) {
                $query->where('business_id', $business_id);
            }

            return $query->get();
        }
        return view('admin.charges.index');
    }

    public function pending()
    {
        return view('admin.charges.pending');
    }

    public function pendingData(Request $request, Business $business)
    {
        $startDate = new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York');
        $endDate = new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York');

        $processor = new PaymentProcessor($business, $startDate, $endDate, logger());
        return $processor->getPaymentModels();
    }

    public function pendingDataPerClient(Request $request, Business $business)
    {
        $startDate = new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York');
        $endDate = new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York');

        $processor = new PaymentProcessor($business, $startDate, $endDate, logger());
        return $processor->getPaymentDataPerClient();
    }

    public function processCharges(Request $request, Business $business)
    {
        $startDate = new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York');
        $endDate = new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York');

        $processor = new PaymentProcessor($business, $startDate, $endDate, logger());
        $count = $processor->process();
        return new SuccessResponse('There were ' . $count . ' successful transactions.');
    }

    /**
     * @deprecated NOT USED ANYMORE, CHARGES ARE DONE IN BUSINESS BATCHES
     * @param \Illuminate\Http\Request $request
     * @param \App\Client $client
     * @return \App\Responses\CreatedResponse|\App\Responses\ErrorResponse
     */
    public function chargeClient(Request $request, Client $client)
    {
        $startDate = new Carbon($request->input('start_date'), 'America/New_York');
        $endDate = new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York');
        $payment = new ClientPaymentAggregator($client, $startDate, $endDate);
        if ($transaction = $payment->charge()) {
            return new CreatedResponse('The client has been charged ' . $transaction->amount, $transaction);
        }
        return new ErrorResponse(500, 'System error charging client.');
    }
}
