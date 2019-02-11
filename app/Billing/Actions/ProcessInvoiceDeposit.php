<?php


namespace App\Billing\Actions;


use App\Billing\BusinessInvoice;
use App\Billing\CaregiverInvoice;
use App\Billing\Contracts\DepositInvoiceInterface;
use App\Billing\Deposit;
use App\Billing\Exceptions\PaymentMethodError;
use App\Billing\Payments\Contracts\DepositMethodStrategy;

class ProcessInvoiceDeposit
{

    /**
     * @var \App\Billing\Actions\ProcessDeposit
     */
    protected $depositProcessor;
    /**
     * @var \App\Billing\Actions\ApplyDeposit
     */
    protected $depositApplicator;

    public function __construct(ProcessDeposit $depositProcessor, ApplyDeposit $depositApplicator)
    {
        $this->depositProcessor = $depositProcessor;
        $this->depositApplicator = $depositApplicator;
    }

    public function payInvoice(DepositInvoiceInterface $invoice, ?DepositMethodStrategy $strategy = null, ?float $amount = null): Deposit
    {
        if ($amount === null) {
            $amount = $invoice->getAmountDue();
        }

        $deposit = null;
        if ($invoice instanceof CaregiverInvoice) {
            $deposit = $this->depositProcessor->depositToCaregiver($invoice->caregiver, $strategy, $amount);
        }
        else if ($invoice instanceof BusinessInvoice) {
            $deposit = $this->depositProcessor->depositToBusiness($invoice->business, $strategy, $amount);
        }

        if (!$deposit) {
            throw new PaymentMethodError("Unable to process deposit for invoice.");
        }

        $this->depositApplicator->apply($invoice, $deposit, $amount);
        return $deposit;
    }

}