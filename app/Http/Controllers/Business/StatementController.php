<?php
namespace App\Http\Controllers\Business;

use App\Billing\Deposit;
use App\Billing\Payment;
use App\Billing\View\DepositViewGenerator;
use App\Billing\View\HtmlViewStrategy;
use App\Billing\View\PaymentViewGenerator;
use App\Billing\View\PdfViewStrategy;

class StatementController extends BaseController
{
    public function payment(Payment $payment, string $view = "html")
    {
        $this->authorize('read', $payment);

        $strategy = new HtmlViewStrategy();
        if (strtolower($view) === 'pdf') {
            $strategy = new PdfViewStrategy('payment-' . $payment->id . '.pdf');
        }

        $viewGenerator = new PaymentViewGenerator($strategy);
        return $viewGenerator->generate($payment);
    }

    public function deposit(Deposit $deposit, string $view = "html")
    {
        $this->authorize('read', $deposit);

        $strategy = new HtmlViewStrategy();
        if (strtolower($view) === 'pdf') {
            $strategy = new PdfViewStrategy('deposit-' . $deposit->id . '.pdf');
        }

        $viewGenerator = new DepositViewGenerator($strategy);
        return $viewGenerator->generate($deposit);
    }
}