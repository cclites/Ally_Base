<?php

namespace App\Billing\Events;

use App\Billing\Contracts\InvoiceableInterface;
use App\Billing\Deposit;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

/**
 * Class InvoiceableInvoiced
 * Emitted when a deposit is removed from a deposit invoice the invoiceable is attached to.  (Commonly a deposit failure)
 *
 * @package App\Billing\Events
 */
final class InvoiceableDepositRemoved implements InvoiceableEvent, DepositEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $invoiceable;
    private $deposit;

    public function __construct(InvoiceableInterface $invoiceable, Deposit $deposit)
    {
        $this->invoiceable = $invoiceable;
        $this->deposit = $deposit;
    }


    public function getInvoiceable(): InvoiceableInterface
    {
        return $this->invoiceable;
    }

    public function getDeposit(): Deposit
    {
        return $this->deposit;
    }
}
