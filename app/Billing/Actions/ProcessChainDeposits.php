<?php
namespace App\Billing\Actions;

use App\Billing\DepositLog;
use App\Billing\Gateway\ACHDepositInterface;
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
     * @var \App\Billing\Actions\DepositInvoiceAggregator
     */
    protected $invoiceAggregator;
    /**
     * @var \App\Billing\Actions\ApplyExistingDeposits
     */
    protected $applyExistingDeposits;


    function __construct(ACHDepositInterface $achGateway = null, ProcessInvoiceDeposit $depositProcessor = null,
        DepositInvoiceAggregator $invoiceAggregator = null, ApplyExistingDeposits $applyExistingDeposits = null)
    {
        $this->achGateway = $achGateway ?: app(ACHDepositInterface::class);
        $this->depositProcessor = $depositProcessor ?: app(ProcessInvoiceDeposit::class);
        $this->invoiceAggregator = $invoiceAggregator ?: app(DepositInvoiceAggregator::class);
        $this->applyExistingDeposits = $applyExistingDeposits ?: app(ApplyExistingDeposits::class);
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

        $this->applyExistingDeposits->toChain($chain);

        $invoices = $this->invoiceAggregator->dueForChain($chain);
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

}