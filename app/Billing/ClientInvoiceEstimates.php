<?php
namespace App\Billing;

use App\Billing\Exceptions\PaymentMethodError;
use App\Billing\Payments\Methods\CreditCard;

class ClientInvoiceEstimates
{
    /**
     * @var \App\Billing\ClientInvoice
     */
    protected $invoice;

    public function __construct(ClientInvoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function getPaymentMethod()
    {
        try {
            if ($method = $this->invoice->getClientPayer()->getPaymentMethod()) {
                return $method;
            }
        }
        catch (PaymentMethodError $e) {}
        return new CreditCard(); // default method for calculations
    }

    public function getAllyTotal()
    {
        return $this->invoice->getItems()->reduce(function($carry, ClientInvoiceItem $item) {
            if (!$item->getInvoiceable()) {
                return $carry;
            }
            return add($carry, $this->getPaymentMethod()->getAllyFee($item->amount_due, true));
        }, 0);
    }

    public function getCaregiverTotal()
    {
        return $this->invoice->getItems()->reduce(function($carry, ClientInvoiceItem $item) {
            if (!$item->getInvoiceable()) {
                return $carry;
            }
            return add($carry, multiply($item->getInvoiceable()->getCaregiverRate(), $item->getInvoiceable()->getItemUnits()));
        }, 0);
    }

    public function getProviderTotal()
    {
        if ($this->getAllyTotal() == 0) {
            return 0;
        }

        return subtract($this->invoice->getAmount(), add($this->getAllyTotal(), $this->getCaregiverTotal()));
    }
}