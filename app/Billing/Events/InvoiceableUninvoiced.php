<?php
namespace App\Billing\Events;


use App\Billing\Contracts\InvoiceableInterface;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceableUninvoiced implements InvoiceableEvent
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