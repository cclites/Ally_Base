<?php

namespace App\Billing\Events;

use App\Billing\Contracts\InvoiceableInterface;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

/**
 * Class InvoiceableInvoiced
 * Emitted when the Invoiceable is fully invoiced to a client (fully meaning multiple invoices if the client has a split payer strategy)
 *
 * @package App\Billing\Events
 */
final class InvoiceableInvoiced implements InvoiceableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $invoiceable;

    public function __construct(InvoiceableInterface $invoiceable)
    {
        $this->invoiceable = $invoiceable;
    }


    public function getInvoiceable(): InvoiceableInterface
    {
        return $this->invoiceable;
    }

}
