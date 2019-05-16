<?php


namespace App\Billing\Actions;


use App\Billing\BusinessInvoice;
use App\Billing\CaregiverInvoice;
use App\Billing\Contracts\DepositInvoiceInterface;
use App\Billing\Deposit;
use App\Billing\Exceptions\PaymentAmountError;
use App\Billing\Exceptions\PaymentMethodError;
use App\Billing\Payments\Contracts\DepositMethodStrategy;
use App\Billing\Payments\DepositMethodFactory;
use App\Business;
use App\Caregiver;

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

    /**
     * Submit a deposit for a single invoice
     *
     * @param DepositInvoiceInterface $invoice
     * @param DepositMethodFactory $methodFactory
     * @param float|null $amount
     * @return Deposit
     * @throws PaymentAmountError
     * @throws PaymentMethodError
     */
    public function payInvoice(DepositInvoiceInterface $invoice, DepositMethodFactory $methodFactory, ?float $amount = null): Deposit
    {
        if ($amount === null) {
            $amount = $invoice->getAmountDue();
        }

        $deposit = null;
        if ($invoice instanceof CaregiverInvoice) {
            $deposit = $this->depositProcessor->depositToCaregiver($invoice->caregiver, $methodFactory, $amount);
        }
        else if ($invoice instanceof BusinessInvoice) {
            $deposit = $this->depositProcessor->depositToBusiness($invoice->business, $methodFactory, $amount);
        }

        if (!$deposit) {
            throw new PaymentMethodError("Unable to process deposit for invoice.");
        }

        $this->depositApplicator->apply($invoice, $deposit, $amount);
        return $deposit;
    }

    /**
     * Submit a single deposit for multiple invoices of the same recipient
     *
     * @param DepositInvoiceInterface[] $invoices
     * @param DepositMethodFactory $methodFactory
     * @return Deposit
     * @throws PaymentAmountError
     * @throws PaymentMethodError
     */
    public function payInvoices(iterable $invoices, DepositMethodFactory $methodFactory): Deposit
    {
        $amount = 0.0;
        foreach($invoices as $invoice) {
            $amount = add($amount, $invoice->getAmountDue());
        }
        if ($amount <= 0) {
            throw new PaymentAmountError("The invoices had less than or equal to $0 due.");
        }

        $recipient = null;
        foreach($invoices as $invoice) {
            if ($recipient === null) {
                $recipient = $invoice->getRecipient();
            } else if ($recipient != $invoice->getRecipient()) {
                throw new PaymentMethodError("The deposit recipients do not match.");
            }
        }

        $deposit = null;
        if ($recipient instanceof Caregiver) {
            $deposit = $this->depositProcessor->depositToCaregiver($recipient, $methodFactory, $amount);
        }
        else if ($recipient instanceof Business) {
            $deposit = $this->depositProcessor->depositToBusiness($recipient, $methodFactory, $amount);
        }

        if (!$deposit) {
            throw new PaymentMethodError("Unable to process deposit for invoice.");
        }

        foreach($invoices as $invoice) {
            $this->depositApplicator->apply($invoice, $deposit, $invoice->getAmountDue());
        }
        return $deposit;
    }

}