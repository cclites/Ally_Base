<?php

namespace App\Http\Controllers\Admin;

use App\Business;
use App\Client;
use App\Payment;
use App\Payments\ClientPaymentAggregator;
use App\Payments\PaymentProcessor;
use App\Payments\PendingPayments;
use App\Payments\SinglePaymentProcessor;
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

            $query = Payment::with(['transaction', 'client', 'business', 'transaction.lastHistory'])
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

        $processor = new PaymentProcessor($business, $startDate, $endDate);
        return $processor->getPaymentModels();
    }

    public function pendingDataPerClient(Request $request, Business $business)
    {
        $startDate = new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York');
        $endDate = new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York');

        $processor = new PaymentProcessor($business, $startDate, $endDate);
        return $processor->getPaymentDataPerClient();
    }

    public function processCharges(Request $request, Business $business)
    {
        $startDate = new Carbon($request->input('start_date') . ' 00:00:00', 'America/New_York');
        $endDate = new Carbon($request->input('end_date') . ' 23:59:59', 'America/New_York');

        $processor = new PaymentProcessor($business, $startDate, $endDate);
        $count = $processor->process();
        return new SuccessResponse('There were ' . $count . ' successful transactions.');
    }

    public function manualCharge(Request $request)
    {
        $request->validate([
            'business_id' => 'required_without:client_id',
            'client_id' => 'required_without:business_id',
            'amount' => 'required|numeric|min:0.1|max:10000',
            'adjustment' => 'nullable|boolean',
            'notes' => 'nullable|max:1024',
        ]);

        if ($request->client_id) {
            $client = Client::findOrFail($request->client_id);
            if (!$client->defaultPayment) return new ErrorResponse(400, 'Client does not have a payment method.');
            $transaction = SinglePaymentProcessor::chargeClient($client, $request->amount, $request->adjustment ?? false, $request->notes);
        }
        else if ($request->business_id) {
            $business = Business::findOrFail($request->business_id);
            if (!$business->paymentAccount) return new ErrorResponse(400, 'Business does not have a payment account.');
            $transaction = SinglePaymentProcessor::chargeBusiness($business, $request->amount, $request->adjustment ?? false, $request->notes);
        }

        if ($transaction) {
            return new SuccessResponse('Transaction processed for $' . $request->amount);
        }
        return new ErrorResponse(400, 'Transaction failure');
    }

    public function markSuccessful(Payment $payment)
    {
        if ($payment->transaction) {
            $payment->transaction->update(['success' => true]);
        }
        $payment->update(['success' => true]);
        foreach($payment->shifts as $shift) {
            $shift->statusManager()->ackPayment($payment->id);
        }
        $msg = 'Payment marked as successful.';
        if ($payment->client && $payment->client->isOnHold()) {
            $msg .= 'This client is still on hold.';
        }
        else if ($payment->business && $payment->business->isOnHold()) {
            $msg .= 'This business is still on hold.';
        }
        return new SuccessResponse($msg);
    }

    public function markFailed(Payment $payment)
    {
        if ($payment->transaction) {
            $payment->transaction->update(['success' => false]);
        }
        $payment->update(['success' => false]);
        foreach($payment->shifts as $shift) {
            $shift->statusManager()->ackReturnedPayment();
        }
        if ($payment->client) {
            $payment->client->addHold();
            $entity = 'client';
        }
        else if ($payment->business) {
            $payment->business->addHold();
            $entity = 'registry';
        }
        return new SuccessResponse('Payment marked as failed.  This ' . $entity . ' has been placed on hold.');
    }
}
