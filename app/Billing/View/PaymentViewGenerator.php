<?php
namespace App\Billing\View;

use App\Billing\Payment;
use App\Billing\View\Data\PaymentInvoiceData;
use App\Businesses\NullContact;
use App\Contracts\ContactableInterface;
use App\Contracts\ViewStrategy;

class PaymentViewGenerator
{
    protected $strategy;

    function __construct(PaymentViewStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    function generate(Payment $payment, string $viewName = "statements.payment")
    {
        $invoiceObjects = [];
        foreach($payment->invoices as $invoice) {
            $invoiceObject = new PaymentInvoiceData(
                $invoice,
                (float) $invoice->pivot->amount_applied
            );
            $invoiceObjects[] = $invoiceObject;
        }

        $payer = $this->buildContact($payment);

        return $this->strategy->generate($payer, $payment, $invoiceObjects);
    }

    protected function buildContact(Payment $payment): ContactableInterface
    {
        if ($method = $payment->getPaymentMethod()) {
            if ($method->getOwnerModel() instanceof ContactableInterface) {
                return $method->getOwnerModel();
            }

            return new NullContact($method->getBillingName(), $method->getBillingAddress(), $method->getBillingPhone());
        }

        return new NullContact();
    }
}