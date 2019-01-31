<?php
namespace App\Billing\Actions;

use App\Billing\ClientInvoice;
use App\Billing\Contracts\ChargeableInterface;
use App\Billing\Exceptions\PaymentMethodError;
use App\Billing\Gateway\ACHDepositInterface;
use App\Billing\Gateway\CreditCardPaymentInterface;
use App\Billing\Payments\BankAccountPayment;
use App\Billing\Payments\CreditCardPayment;
use App\Billing\Payments\Methods\BankAccount;
use App\Billing\Payments\Methods\CreditCard;
use App\Billing\Payments\Methods\ProviderPayment;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Business;
use App\BusinessChain;
use Illuminate\Support\Collection;

class ProcessChainPayments
{
    /**
     * @var \App\Billing\Gateway\ACHDepositInterface
     */
    protected $achGateway;

    /**
     * @var \App\Billing\Actions\ProcessInvoiceDeposit
     */
    protected $depositProcessor;

    /**
     * @var \App\Billing\Queries\ClientInvoiceQuery
     */
    protected $invoiceQuery;

    function __construct(ACHDepositInterface $achGateway = null, ProcessInvoiceDeposit $depositProcessor = null, ClientInvoiceQuery $invoiceQuery = null)
    {
        $this->achGateway = $achGateway ?: app(ACHDepositInterface::class);
        $this->depositProcessor = $depositProcessor ?: app(ProcessInvoiceDeposit::class);
        $this->invoiceQuery = $invoiceQuery ?: app(ClientInvoiceQuery::class);
    }

    /**
     * Process the payments for the chain, grouping by payment method
     *
     * @param \App\BusinessChain $chain
     * @throws \App\Billing\Exceptions\PaymentMethodDeclined
     * @throws \App\Billing\Exceptions\PaymentMethodError
     */
    function processPayments(BusinessChain $chain)
    {
        $groupedInvoices = $this->groupByPaymentMethod(
            $this->getInvoices($chain)
        );

        foreach($groupedInvoices as $hash => $invoices) {
            /** @var \App\Billing\ClientInvoice[] $invoices */
            $paymentMethod = $this->getPaymentMethod($invoices[0]);
            $strategy = $this->buildStrategy($paymentMethod);
            $this->depositProcessor->payInvoices($invoices, $strategy);
        }
    }

    /**
     * Get all unpaid invoices for a chain
     *
     * @param \App\BusinessChain $chain
     * @return \Illuminate\Support\Collection
     */
    function getInvoices(BusinessChain $chain): Collection
    {
        return $this->invoiceQuery->forBusinessChain($chain)->notPaidInFull()->get();
    }

    /**
     * Group a collection of invoices by payment method
     *
     * @param \App\Billing\ClientInvoice[] $invoices
     * @return array
     */
    function groupByPaymentMethod(iterable $invoices): array
    {
        $hashTable = [];
        foreach($invoices as $invoice) {
            $method = $this->getPaymentMethod($invoice);
            $hash = $method->getHash();
            $hashTable[$hash][] = $invoice;
        }

        return $hashTable;
    }

    /**
     * Get the assigned payment method for an invoice (derived from the client payer)
     *
     * @param \App\Billing\ClientInvoice $invoice
     * @return \App\Billing\Contracts\ChargeableInterface
     * @throws \App\Billing\Exceptions\PaymentMethodError
     */
    function getPaymentMethod(ClientInvoice $invoice): ChargeableInterface
    {
        $paymentMethod = $invoice->getClientPayer()->getPaymentMethod();
        if (!$paymentMethod) {
            throw new PaymentMethodError("Unable to get payment method from invoice.");
        }

        return $paymentMethod;
    }

    /**
     * Build a payment method strategy for the given payment method using the injected gateways
     *
     * @param \App\Billing\Contracts\ChargeableInterface $chargeable
     * @return \App\Billing\Payments\BankAccountPayment|\App\Billing\Payments\CreditCardPayment|\App\Billing\Payments\Methods\ProviderPayment
     * @throws \App\Billing\Exceptions\PaymentMethodError
     */
    function buildStrategy(ChargeableInterface $chargeable)
    {
        if ($chargeable instanceof CreditCard) {
            return new CreditCardPayment($chargeable, $this->ccGateway);
        }
        if ($chargeable instanceof BankAccount) {
            return new BankAccountPayment($chargeable, $this->achGateway);
        }
        if ($chargeable instanceof Business) {
            return new ProviderPayment($chargeable, $this->achGateway);
        }
        throw new PaymentMethodError("Unable to build payment strategy.");
    }
}