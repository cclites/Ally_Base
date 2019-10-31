<?php
namespace App\Billing\View\Html;


use App\Billing\Payment;
use App\Billing\View\PaymentViewStrategy;
use App\Contracts\ContactableInterface;

class HtmlPaymentView implements PaymentViewStrategy
{
    private $view;

    function __construct(string $view = "statements.payment")
    {
        $this->view = $view;
    }

    /**
     * @param \App\Contracts\ContactableInterface $payer
     * @param \App\Billing\Payment $payment
     * @param \App\Billing\View\Data\PaymentInvoiceData[] $invoiceObjects
     * @return mixed
     */
    function generate(ContactableInterface $payer, Payment $payment, array $invoiceObjects)
    {
        $render = 'html';
        return response(view($this->view, compact("payer", "payment", "invoiceObjects", 'render')));
    }
}