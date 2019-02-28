<?php
namespace App\Http\Controllers\Business;

use App\Billing\Deposit;
use App\Billing\Payment;
use App\Billing\View\DepositViewGenerator;
use App\Billing\View\Html\HtmlDepositView;
use App\Billing\View\Html\HtmlPaymentView;
use App\Billing\View\PaymentViewGenerator;
use App\Billing\View\Pdf\PdfDepositView;
use App\Billing\View\Pdf\PdfPaymentView;

class StatementController extends BaseController
{
    public function payment(Payment $payment, string $view = "html")
    {
        $this->authorize('read', $payment);

        $strategy = new HtmlPaymentView();
        if (strtolower($view) === 'pdf') {
            $strategy = new PdfPaymentView('payment-' . $payment->id . '.pdf');
        }

        $viewGenerator = new PaymentViewGenerator($strategy);
        return $viewGenerator->generate($payment);
    }

    public function deposit(Deposit $deposit, string $view = "html")
    {
        $this->authorize('read', $deposit);

        $strategy = new HtmlDepositView();
        if (strtolower($view) === 'pdf') {
            $strategy = new PdfDepositView('deposit-' . $deposit->id . '.pdf');
        }

        $viewGenerator = new DepositViewGenerator($strategy);
        return $viewGenerator->generate($deposit);
    }
}