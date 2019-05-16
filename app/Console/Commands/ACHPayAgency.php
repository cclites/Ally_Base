<?php

namespace App\Console\Commands;

use App\Billing\Actions\ApplyDeposit;
use App\Billing\Actions\DepositInvoiceAggregator;
use App\Billing\Actions\ProcessDeposit;
use App\Billing\Gateway\ACHDepositInterface;
use App\Billing\Payments\DepositMethodFactory;
use App\BusinessChain;
use Illuminate\Console\Command;

class ACHPayAgency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ach:pay_agency_deposit {chain_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate caregiver and business deposits into one agency payment';

    /**
     * @var DepositInvoiceAggregator
     */
    private $invoiceAggregator;

    /**
     * @var ProcessDeposit
     */
    private $depositProcessor;

    /**
     * @var DepositMethodFactory
     */
    private $methodFactory;

    /**
     * @var ApplyDeposit
     */
    private $depositApplicator;

    /**
     * Create a new command instance.
     *
     * @param DepositInvoiceAggregator $aggregator
     * @param ProcessDeposit $depositProcessor
     * @param ApplyDeposit $depositApplicator
     * @param DepositMethodFactory|null $methodFactory
     */
    public function __construct(DepositInvoiceAggregator $aggregator, ProcessDeposit $depositProcessor, ApplyDeposit $depositApplicator, DepositMethodFactory $methodFactory = null)
    {
        parent::__construct();
        $this->invoiceAggregator = $aggregator;
        $this->depositProcessor = $depositProcessor;
        $this->depositApplicator = $depositApplicator;
        $this->methodFactory = $methodFactory ?: new DepositMethodFactory(app(ACHDepositInterface::class));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \App\Billing\Exceptions\PaymentMethodError
     */
    public function handle()
    {
        $chain = BusinessChain::findOrFail($this->argument('chain_id'));
        if (!$this->confirm("Are you sure you wish to process a single agency deposit for {$chain->name}?")) {
            $this->info("Cancelled.");
            return false;
        }


        if ($chain->businesses->count() !== 1) {
            // This is due to the complexities of caregiver assignments and caregiver invoices being an aggregation of data for ALL chains and business locations they work for.
            $this->error("This command can only work for a chain that has one business location.");
            return false;
        }

        $caregivers = $this->invoiceAggregator->getEligibleCaregivers($chain);
        foreach($caregivers as $caregiver) {
            if ($caregiver->businessChains->count() > 1) {
                // This is due to the complexities of caregiver assignments and caregiver invoices being an aggregation of data for ALL chains and business locations they work for.
                $this->error("This command can only work for a caregivers that are assigned to one business chain.  {$caregiver->name()} is assigned to multiple.");
                return false;
            }
        }

        if (!$bankAccount = $chain->businesses->first()->bankAccount) {
            $this->error("Missing business bank account.");
            return false;
        }

        $invoices = $this->invoiceAggregator->dueForChain($chain);
        if (!$invoices->count()) {
            $this->error("No deposit invoices found.");
            return false;
        }

        $amount = 0.0;
        foreach($invoices as $invoice) {
            $amount = add($amount, $invoice->getAmountDue());
        }
        if ($amount <= 0) {
            $this->error("The invoices had less than or equal to $0 due.");
            return false;
        }

        $deposit = $this->depositProcessor->depositToBusiness($chain->businesses->first(), $this->methodFactory, $amount);
        if ($deposit) {
            $this->info("Deposit ID {$deposit->id} processed for \${$deposit->amount}.");
            try {
                foreach($invoices as $invoice) {
                    $this->depositApplicator->apply($invoice, $deposit, $invoice->getAmountDue());
                }
                $this->info("Successfully applied deposit to all invoices.");
            }
            catch (\Exception $e) {
                $this->error($e->getMessage());
                $this->error("The deposit was not successfully applied to all invoices, manual intervention is required.");
                $this->error("DO NOT RE-RUN THIS COMMAND AS THE DEPOSIT HAS ALREADY BEEN PROCESSED.");
                return false;
            }
        } else {
            $this->error("Error processing deposit");
            return false;
        }

        return true;
    }
}
