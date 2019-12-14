<?php
namespace App\Billing\View;

use App\Billing\BusinessInvoice;
use App\Billing\CaregiverInvoice;
use App\Billing\Claim;
use App\Billing\ClientInvoice;
use App\Billing\Contracts\InvoiceInterface;
use App\Billing\OfflineInvoicePayment;
use App\Billing\Payer;
use App\Businesses\NullContact;
use App\Contracts\ContactableInterface;
use Illuminate\Support\Collection;

class InvoiceViewGenerator
{
    /**
     * @var \App\Contracts\ViewStrategy
     */
    protected $strategy;

    function __construct(InvoiceViewStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    function generate(ContactableInterface $sender, ContactableInterface $recipient, ContactableInterface $subject, InvoiceInterface $invoice, Collection $payments, string $override_ally_logo = null)
    {
        return $this->strategy->generate($invoice, $sender, $recipient, $subject, $payments, $override_ally_logo);
    }

    function generateClientInvoice(ClientInvoice $clientInvoice)
    {
        $client = $clientInvoice->client;
        $clientPayer = $clientInvoice->getClientPayer();
        $business = $client->business;

        if ($clientInvoice->isOffline()) {
            // TODO: refactor this into a Payment interface with methods to get these fields
            $payments = $clientInvoice->OfflinePayments->map(function (OfflineInvoicePayment $payment) {
                return (object)[
                    'created_at'=> $payment->payment_date,
                    'payment_type' => $payment->description.' ('.$payment->type.': '.$payment->reference.')',
                    'description' => $payment->description,
                    'amount' => $payment->amount,
                    'pivot' => [
                        'invoice_id' => $payment->invoice->id,
                        'payment_id' => $payment->id,
                        'amount_applied' => $payment->amount,
                    ],
                ];
            });
        } else {
            $payments = $clientInvoice->payments;
        }

        if ($clientPayer == null || $clientPayer->payer_id === Payer::PRIVATE_PAY_ID) {
            $recipient = $client;
            $subject = new NullContact();
        } else {
            $recipient = $clientPayer->payer;
            $subject = $client;
        }

        return $this->generate(
            $business,
            $recipient,
            $subject,
            $clientInvoice,
            $payments,
            $business->logo
        );
    }

    function generateClaimInvoice(Claim $claim)
    {
        $client = $claim->invoice->client;
        $clientPayer = $claim->invoice->getClientPayer();
        $business = $client->business;
        $payments = $claim->payments;

        if ($clientPayer == null || $clientPayer->payer_id === Payer::PRIVATE_PAY_ID) {
            $recipient = $client;
            $subject = new NullContact();
        } else {
            $recipient = $clientPayer->payer;
            $subject = $client;
        }

        return $this->generate(
            $business,
            $recipient,
            $subject,
            $claim,
            $payments
        );
    }

    function generateCaregiverInvoice(CaregiverInvoice $caregiverInvoice)
    {
        $caregiver = $caregiverInvoice->caregiver;
        $payments = $caregiverInvoice->deposits;

        return $this->generate(
            new NullContact(),
            $caregiver,
            new NullContact(),
            $caregiverInvoice,
            $payments
        );
    }

    function generateBusinessInvoice(BusinessInvoice $businessInvoice)
    {
        $business = $businessInvoice->business;
        $payments = $businessInvoice->deposits;

        return $this->generate(
            new NullContact(),
            $business,
            new NullContact(),
            $businessInvoice,
            $payments
        );
    }
}