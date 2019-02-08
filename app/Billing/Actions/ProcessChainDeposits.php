<?php
namespace App\Billing\Actions;

use App\Billing\ClientInvoice;
use App\Billing\Contracts\ChargeableInterface;
use App\Billing\Contracts\DepositInvoiceInterface;
use App\Billing\DepositLog;
use App\Billing\Exceptions\PaymentMethodError;
use App\Billing\Gateway\ACHDepositInterface;
use App\Billing\Gateway\CreditCardPaymentInterface;
use App\Billing\Payments\BankAccountPayment;
use App\Billing\Payments\CreditCardPayment;
use App\Billing\Payments\Methods\BankAccount;
use App\Billing\Payments\Methods\CreditCard;
use App\Billing\Payments\Methods\ProviderPayment;
use App\Billing\Queries\BusinessInvoiceQuery;
use App\Billing\Queries\CaregiverInvoiceQuery;
use App\Billing\Queries\ClientInvoiceQuery;
use App\Business;
use App\BusinessChain;
use Illuminate\Support\Collection;

class ProcessChainDeposits
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
     * @var \App\Billing\Queries\CaregiverInvoiceQuery
     */
    protected $caregiverInvoiceQuery;

    /**
     * @var \App\Billing\Queries\BusinessInvoiceQuery
     */
    protected $businessInvoiceQuery;


    function __construct(ACHDepositInterface $achGateway = null, ProcessInvoiceDeposit $depositProcessor = null,
        CaregiverInvoiceQuery $caregiverInvoiceQuery = null, BusinessInvoiceQuery $businessInvoiceQuery = null)
    {
        $this->achGateway = $achGateway ?: app(ACHDepositInterface::class);
        $this->depositProcessor = $depositProcessor ?: app(ProcessInvoiceDeposit::class);
        $this->caregiverInvoiceQuery = $caregiverInvoiceQuery ?: app(CaregiverInvoiceQuery::class);
        $this->businessInvoiceQuery = $businessInvoiceQuery ?: app(BusinessInvoiceQuery::class);
    }

    /**
     * Process the deposits for the chain
     *
     * @param \App\BusinessChain $chain
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    function processDeposits(BusinessChain $chain): Collection
    {
        DepositLog::acquireLock();
        $batchId = DepositLog::getNextBatch($chain->id);

        $invoices = $this->getInvoices($chain);
        $results = [];
        foreach($invoices as $invoice) {
            $log = new DepositLog();
            $log->batch_id = $batchId;
            try {
                $deposit = $this->depositProcessor->payInvoice($invoice);
                $log->setDeposit($deposit);
                if ($deposit->transaction && $deposit->transaction->method) {
                    $log->setPaymentMethod($deposit->transaction->method);
                }
            }
            catch (\Exception $e) {
                $log->setException($e);
            }

            $results[] = $log;
        }

        DepositLog::releaseLock();

        return collect($results);
    }

    /**
     * Get all unpaid invoices for a chain
     *
     * @param \App\BusinessChain $chain
     * @return \Illuminate\Support\Collection|DepositInvoiceInterface[]
     */
    function getInvoices(BusinessChain $chain): Collection
    {
        $caregiverInvoices = $this->caregiverInvoiceQuery->forBusinessChain($chain)->notPaidInFull()->get();
        $businessInvoices = $this->businessInvoiceQuery->forBusinessChain($chain)->notPaidInFull()->get();
        return $caregiverInvoices->merge($businessInvoices);
    }
}