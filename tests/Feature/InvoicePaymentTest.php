<?php

namespace Tests\Feature;

use App\Billing\Actions\ProcessInvoicePayment;
use App\Billing\Contracts\ChargeableInterface;
use App\Billing\GatewayTransaction;
use App\Billing\Generators\ClientInvoiceGenerator;
use App\Billing\Payment;
use App\Billing\Payments\Contracts\PaymentMethodStrategy;
use App\Billing\Payments\Methods\BankAccount;
use App\Billing\Payments\Methods\CreditCard;
use App\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesClientInvoiceResources;
use Tests\TestCase;

class InvoicePaymentTest extends TestCase
{
    use RefreshDatabase;
    use CreatesClientInvoiceResources;

    /** @var \App\Client */
    private $client;

    /** @var ClientInvoiceGenerator */
    private $invoicer;

    /**
     * @var ProcessInvoicePayment
     */
    private $processor;

    /**
     * @var \App\Billing\ClientPayer
     */
    private $clientPayer;

    protected function setUp()
    {
        parent::setUp();

        $this->client = factory(Client::class)->create();
        $this->clientPayer = $this->createBalancePayer();
        $this->invoicer = app(ClientInvoiceGenerator::class);
        $this->processor = app(ProcessInvoicePayment::class);
    }


    /**
     * @test
     */
    function a_payment_can_be_processed_from_a_single_invoice()
    {
        $this->createService(20.00);
        $invoice = $this->invoicer->generateAll($this->client)[0];

        $payment = $this->processor->payInvoice($invoice, new DummyCreditCard());

        $this->assertEquals(20.00, $payment->amount);
        $this->assertCount(1, $payment->invoices);
    }

    /**
     * @test
     */
    function a_payment_records_the_corresponding_ally_fee()
    {
        $this->createService(100.00);
        $invoice = $this->invoicer->generateAll($this->client)[0];

        $payment = $this->processor->payInvoice($invoice, new DummyCreditCard());

        $this->assertEquals(4.76, $payment->getAllyFee());
    }

    /**
     * @test
     */
    function the_invoiceables_should_receive_their_allocated_ally_fees()
    {
        /**
         * Two invoiceables are assigned to an invoice, both should receive a split of the payment's ally fee relating to their amount
         */

        $this->createService(42.00);
        $this->createService(42.00);
        $invoice = $this->invoicer->generateAll($this->client)[0];

        $payment = $this->processor->payInvoice($invoice, new DummyCreditCard());
        $invoiceable1 = $invoice->items[0]->invoiceable;
        $invoiceable2 = $invoice->items[1]->invoiceable;


        $this->assertEquals(4.00, $payment->getAllyFee());
        $this->assertEquals(2.00, $invoiceable1->getAllyRate());
        $this->assertEquals(2.00, $invoiceable2->getAllyRate());
    }

    // TODO: a_payers_previous_unapplied_payments_are_used_first
    // TODO: unapplying_a_payment_should_remove_the_old_allocated_ally_fees
    // TODO: reapplying_a_payment_should_add_the_new_allocated_ally_fees

}

class DummyCreditCard implements PaymentMethodStrategy
{

    public function charge(float $amount, string $currency = "USD"): ?GatewayTransaction
    {
        $transaction = factory(GatewayTransaction::class)->make(['transaction_type' => 'sale', 'amount' => $amount]);
        $transaction->id = 123;
        return $transaction;
    }

    public function refund(
        ?GatewayTransaction $transaction,
        float $amount,
        string $currency = "USD"
    ): ?GatewayTransaction {
        $transaction = factory(GatewayTransaction::class)->make(['transaction_type' => 'credit', 'amount' => $amount]);
        $transaction->id = 123;
        return $transaction;
    }

    public function getPaymentMethod(): ChargeableInterface
    {
        return new CreditCard();
    }

    public function getPaymentType(): string
    {
        return 'CC';
    }
}


class DummyBankAccount extends DummyCreditCard
{
    public function getPaymentMethod(): ChargeableInterface
    {
        return new BankAccount();
    }

    public function getPaymentType(): string
    {
        return 'ACH';
    }
}