<?php
namespace App\Billing\Actions;

use App\Billing\DepositLog;
use App\Billing\Gateway\ACHDepositInterface;
use App\Billing\Payments\DepositMethodFactory;
use App\BusinessChain;
use Illuminate\Support\Collection;

class ProcessChainDeposits
{
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
    /**
     * @var \App\Billing\Payments\DepositMethodFactory
     */
    protected $methodFactory;


    function __construct(DepositMethodFactory $methodFactory = null, ProcessInvoiceDeposit $depositProcessor = null,
        DepositInvoiceAggregator $invoiceAggregator = null, ApplyExistingDeposits $applyExistingDeposits = null)
    {
        $this->methodFactory = $methodFactory ?: new DepositMethodFactory(app(ACHDepositInterface::class));
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

        $results = [];

        $caregivers = $this->invoiceAggregator->getEligibleCaregivers($chain);
        foreach($caregivers as $caregiver) {
            $invoices = $this->invoiceAggregator->dueForCaregiver($caregiver);
            $results[] = $this->processSingleDeposit($batchId, $invoices, $chain->id);
        }

        $businesses = $this->invoiceAggregator->getEligibleBusinesses($chain);
        foreach($businesses as $business) {
            $invoices = $this->invoiceAggregator->dueForBusiness($business);
            $results[] = $this->processSingleDeposit($batchId, $invoices, $chain->id);
        }

        DepositLog::releaseLock();

        return collect($results);
    }

    /**
     * @param string $batchId
     * @param \App\Billing\CaregiverInvoice $invoice
     * @param array $results
     * @return array
     */
    private function processSingleDeposit(string $batchId, iterable $invoices, $chainId): DepositLog
    {
        $log = new DepositLog();
        $log->batch_id = $batchId;
        try {
            $deposit = $this->depositProcessor->payInvoices($invoices, $this->methodFactory, $chainId);
            $log->setDeposit($deposit);
            if ($deposit->transaction && $deposit->transaction->method) {
                $log->setPaymentMethod($deposit->transaction->method);
            }
        } catch (\Exception $e) {
            $log->setException($e);
        }

        return $log;
    }


}