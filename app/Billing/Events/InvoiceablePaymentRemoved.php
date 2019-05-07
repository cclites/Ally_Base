<?php

namespace App\Billing\Events;

use App\Billing\Contracts\InvoiceableInterface;
use App\Billing\Payment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Twilio\TwiML\Voice\Pay;

/**
 * Class InvoiceableInvoiced
 * Emitted when a payment is removed from a payment invoice the invoiceable is attached to.  (Commonly a payment failure)
 *
 * @package App\Billing\Events
 */
final class InvoiceablePaymentRemoved implements InvoiceableEvent, PaymentEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $invoiceable;
    protected $payment;

    public function __construct(InvoiceableInterface $invoiceable, Payment $payment)
    {
        $this->invoiceable = $invoiceable;
        $this->payment = $payment;
    }

    public function getInvoiceable(): InvoiceableInterface
    {
        return $this->invoiceable;
    }

    public function getPayment(): Payment
    {
        return $this->payment;
    }

}
