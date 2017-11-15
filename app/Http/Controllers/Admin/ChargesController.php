<?php

namespace App\Http\Controllers\Admin;

use App\Client;
use App\Payments\ClientPaymentAggregator;
use App\Payments\PendingPayments;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChargesController extends Controller
{

    public function pendingPayments(Request $request)
    {
        if ($request->expectsJson()) {
            $startDate = new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York');
            $endDate = new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York');

            $handler = new PendingPayments($startDate, $endDate);
            return $handler->getData();
        }
        return view('admin.charges.pending');
    }

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
