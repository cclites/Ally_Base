<?php

namespace App\Billing\Events;

use App\Billing\Contracts\InvoiceableInterface;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

/**
 * Class InvoiceableCreated
 * Emitted when the Invoiceable entity is created and persisted to the database
 *
 * @package App\Billing\Events
 */
final class InvoiceableCreated implements InvoiceableEvent
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
