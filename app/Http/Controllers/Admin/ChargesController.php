<?php

namespace App\Http\Controllers\Admin;

use App\Billing\Actions\ProcessChainPayments;
use App\Billing\View\Html\HtmlPaymentView;
use App\Billing\View\PaymentViewGenerator;
use App\Billing\View\Pdf\PdfPaymentView;
use App\Business;
use App\BusinessChain;
use App\Client;
use App\Billing\Payment;
use App\Payments\SinglePaymentProcessor;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\Resources\PaymentLog;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChargesController extends Controller
{
    public function index(Request $request)
    {
        if ($request->expectsJson() && $request->input('json')) {

            $startDate = (new Carbon($request->start_date . ' 00:00:00', 'America/New_York'))->setTimezone('UTC');
            $endDate = (new Carbon($request->end_date . ' 23:59:59', 'America/New_York'))->setTimezone('UTC');

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

    public function show(Payment $payment, string $view = "html")
    {
        $strategy = new HtmlPaymentView();
        if (strtolower($view) === 'pdf') {
            $strategy = new PdfPaymentView('payment-' . $payment->id . '.pdf');
        }

        $viewGenerator = new PaymentViewGenerator($strategy);
        return $viewGenerator->generate($payment);
    }

    public function pending()
    {
        $chains = BusinessChain::ordered()->get();
        return view('admin.charges.pending', compact('chains'));
    }

    public function processCharges(BusinessChain $chain, ProcessChainPayments $action)
    {
        $results = $action->processPayments($chain);
        $collection = PaymentLog::collection($results)->toArray(null);

        return new CreatedResponse('The payments have been processed.', $collection);
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

        if (empty($transaction)) {
            return new ErrorResponse(400, 'Transaction failure.');
        }

        if ($transaction->success) {
            return new SuccessResponse('Transaction processed for $' . $request->amount);
        }

        return new ErrorResponse(400, 'Transaction declined for $' . $request->amount);
    }

    public function markFailed(Payment $payment)
    {
        if ($payment->transaction) {
            $payment->transaction->recordFailure();
        } else {
            $payment->markFailed();
        }

        return new SuccessResponse('Payment marked as failed.  This entity has been placed on hold.');
    }
}
