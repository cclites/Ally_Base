<?php
namespace App\Billing\Actions;

use App\Billing\ClientInvoice;
use App\Billing\Contracts\ChargeableInterface;
use App\Billing\Exceptions\PaymentMethodError;
use App\Billing\Gateway\ACHPaymentInterface;
use App\Billing\Gateway\CreditCardPaymentInterface;
use App\Billing\PaymentLog;
use App\Billing\Payments\Methods\BankAccount;
use App\Billing\Payments\Methods\CreditCard;
use App\Billing\Payments\Methods\Trust;
use App\Billing\Payments\PaymentMethodFactory;
use App\Billing\Queries\OnlineClientInvoiceQuery;
use App\Business;
use App\BusinessChain;
use Illuminate\Support\Collection;

class ProcessChainPayments
{
    /**
     * @var \App\Billing\Actions\ProcessInvoicePayment
     */
    protected $paymentProcessor;
    /**
     * @var \App\Billing\Actions\ApplyPayment
     */
    protected $paymentApplicator;
    /**
     * @var \App\Billing\Queries\OnlineClientInvoiceQuery
     */
    protected $invoiceQuery;
    /**
     * @var \App\Billing\Payments\PaymentMethodFactory
     */
    protected $methodFactory;

    /**
     * @var array
     */
    protected $allowedPaymentMethodTypes = [Business::class, BankAccount::class, CreditCard::class, Trust::class];

    function __construct(PaymentMethodFactory $methodFactory = null, ProcessInvoicePayment $paymentProcessor = null,
        ApplyPayment $paymentApplicator = null, OnlineClientInvoiceQuery $invoiceQuery = null)
    {
        $this->methodFactory = $methodFactory ?: new PaymentMethodFactory(app(ACHPaymentInterface::class), app(CreditCardPaymentInterface::class));
        $this->paymentProcessor = $paymentProcessor ?: app(ProcessInvoicePayment::class);
        $this->paymentApplicator = $paymentApplicator ?: app(ApplyPayment::class);
        $this->invoiceQuery = $invoiceQuery ?: app(OnlineClientInvoiceQuery::class);
    }

    /**
     * Process the payments for the chain, grouping by payment method
     *
     * @param \App\BusinessChain $chain
     * @param array $forPaymentMethodTypes
     * @return Collection|\App\Billing\PaymentLog[]
     * @throws \Exception
     */
    function processPayments(BusinessChain $chain, array $forPaymentMethodTypes = []): Collection
    {
        if (filled($forPaymentMethodTypes)) {
            $this->allowedPaymentMethodTypes = $forPaymentMethodTypes;
        }

        PaymentLog::acquireLock();
        $batchId = PaymentLog::getNextBatch($chain->id);

        $groupedInvoices = $this->groupByPaymentMethod(
            $this->getInvoices($chain)
        );

        $results = [];
        /** @var \App\Billing\ClientInvoice[] $invoices */
        foreach($groupedInvoices as $hash => $invoices) {
            $log = new PaymentLog();
            $log->batch_id = $batchId;
            try {
                if ($hash === 'missing') {
                    throw new PaymentMethodError("Missing payment method for " . $invoices[0]->clientPayer->name());
                }
                $paymentMethod = $this->getPaymentMethod($invoices[0]);
                $log->setPaymentMethod($paymentMethod);
                $strategy = $this->methodFactory->getStrategy($paymentMethod);
                $payment = $this->paymentProcessor->payInvoices($invoices, $strategy);
                $log->setPayment($payment);
            }
            catch (\Exception $e) {
                $log->setException($e);
            }
            $log->save();
            $results[] = $log;
        }

        PaymentLog::releaseLock();

        return collect($results);
    }

    /**
     * Get all unpaid invoices for a chain
     *
     * @param \App\BusinessChain $chain
     * @return \Illuminate\Support\Collection
     */
    function getInvoices(BusinessChain $chain): Collection
    {
        return $this->invoiceQuery
            ->forBusinessChain($chain)
            ->notPaidInFull()
            ->notOnHold()
            ->get();
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
            try {
                $method = $this->getPaymentMethod($invoice);

                // Only include payment methods that are allowed on this
                // current processing call.
                if (! $this->isPaymentMethodAllowed($method)) {
                    continue;
                }

                $hash = $method->getHash();
            }
            catch (\Exception $e) {
                $hash = 'missing';
            }
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
     * Check if the given payment method is allowed.
     *
     * @param ChargeableInterface $method
     * @return bool
     */
    protected function isPaymentMethodAllowed(ChargeableInterface $method) : bool
    {
        foreach ($this->allowedPaymentMethodTypes as $class) {
            if (is_a($method, $class, true)) {
                return true;
            }
        }

        return false;
    }
}