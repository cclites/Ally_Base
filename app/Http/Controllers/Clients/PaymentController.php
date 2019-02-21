<?php
namespace App\Http\Controllers\Clients;

use App\Billing\Payment;
use App\Billing\Queries\PaymentQuery;
use App\Billing\View\HtmlViewStrategy;
use App\Billing\View\PaymentViewGenerator;
use App\Billing\View\PdfViewStrategy;

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

    public function index()
    {
        $client = $this->client();
        $payments = $this->paymentQuery->forClient($client)->get();
        return view('clients.payment_history', compact('client', 'payments'));
    }

    public function show(Payment $payment, string $view = "html")
    {
        $this->authorize('read', $payment);

        $strategy = new HtmlViewStrategy();
        if (strtolower($view) === 'pdf') {
            $strategy = new PdfViewStrategy('payment-' . $payment->id . '.pdf');
        }

        $viewGenerator = new PaymentViewGenerator($strategy);
        return $viewGenerator->generate($payment);
    }
}