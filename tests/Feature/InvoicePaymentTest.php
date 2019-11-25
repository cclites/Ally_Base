<?php

namespace Tests\Feature;

use App\Billing\Actions\ProcessInvoicePayment;
use App\Billing\BillingCalculator;
use App\Billing\Contracts\ChargeableInterface;
use App\Billing\Gateway\ACHDepositInterface;
use App\Billing\Gateway\ACHPaymentInterface;
use App\Billing\Gateway\CreditCardPaymentInterface;
use App\Billing\Gateway\DummyGateway;
use App\Billing\GatewayTransaction;
use App\Billing\Generators\ClientInvoiceGenerator;
use App\Billing\Payments\Contracts\PaymentMethodStrategy;
use App\Billing\Payments\CreditCardPayment;
use App\Billing\Payments\Methods\BankAccount;
use App\Billing\Payments\Methods\CreditCard;
use App\Billing\Payments\PaymentMethodFactory;
use App\Billing\Payments\PaymentMethodType;
use App\Client;
use App\Billing\FeeOverrideRule;
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

    /**
     * @var \App\Business
     */
    private $business;

    /**
     * @var \App\Billing\Payments\PaymentMethodFactory
     */
    protected $methodFactory;

    protected function setUp()
    {
        parent::setUp();

        $this->methodFactory = new PaymentMethodFactory(app(ACHPaymentInterface::class), app(CreditCardPaymentInterface::class));

        $this->client = factory(Client::class)->create();
        $this->business = $this->client->business;
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

        $this->client->setPaymentMethod(factory(CreditCard::class)->create());
        $payment = $this->processor->payInvoice($invoice, $this->methodFactory->getStrategy($this->client->getPaymentMethod()));

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

        $this->client->setPaymentMethod(factory(CreditCard::class)->create());
        $payment = $this->processor->payInvoice($invoice, $this->methodFactory->getStrategy($this->client->getPaymentMethod()));

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

        $this->client->setPaymentMethod(factory(CreditCard::class)->create());
        $payment = $this->processor->payInvoice($invoice, $this->methodFactory->getStrategy($this->client->getPaymentMethod()));

        $invoiceable1 = $invoice->items[0]->invoiceable;
        $invoiceable2 = $invoice->items[1]->invoiceable;

        $this->assertEquals(4.00, $payment->getAllyFee());
        $this->assertEquals(2.00, $invoiceable1->getAllyRate());
        $this->assertEquals(2.00, $invoiceable2->getAllyRate());
    }

    // TODO: a_payers_previous_unapplied_payments_are_used_first
    // TODO: unapplying_a_payment_should_remove_the_old_allocated_ally_fees
    // TODO: reapplying_a_payment_should_add_the_new_allocated_ally_fees

    /**
     * @test
     */
    function the_system_should_calculate_ally_fee_of_a_payment_to_match_the_invoiced_fee()
    {
        $cc = factory(CreditCard::class)->create();
        $amex = factory(CreditCard::class)->create(['type' => 'amex']);
        $bankAccount = factory(BankAccount::class)->create();

        $this->client->setPaymentMethod($cc);
        $service = $this->createService(100.00);
        $invoice = $this->invoicer->generateAll($this->client)[0];
        $this->processor->payInvoice($invoice, $this->methodFactory->getStrategy($this->client->getPaymentMethod()));
        $feeCharged = floatval($service->meta()->where('key', 'ally_fee_charged')->first()->value);
        $this->assertAllyFeeRate(100, BillingCalculator::getCreditCardRate(), $feeCharged);

        $this->client->setPaymentMethod($bankAccount);
        $service = $this->createService(100.00);
        $invoice = $this->invoicer->generateAll($this->client)[0];
        $this->processor->payInvoice($invoice, $this->methodFactory->getStrategy($this->client->getPaymentMethod()));
        $feeCharged = floatval($service->meta()->where('key', 'ally_fee_charged')->first()->value);
        $this->assertAllyFeeRate(100, BillingCalculator::getBankAccountRate(), $feeCharged);

        $this->client->setPaymentMethod($amex);
        $service = $this->createService(100.00);
        $invoice = $this->invoicer->generateAll($this->client)[0];
        $this->processor->payInvoice($invoice, $this->methodFactory->getStrategy($this->client->getPaymentMethod()));
        $feeCharged = floatval($service->meta()->where('key', 'ally_fee_charged')->first()->value);
        $this->assertAllyFeeRate(100, BillingCalculator::getAmexRate(), $feeCharged);
    }

    /** @test */
    function ally_fee_should_use_ach_p_when_business_is_the_payment_method()
    {
        $this->business->setBankAccount('paymentAccount', factory(BankAccount::class)->create(['user_id' => null, 'business_id' => $this->business->id]));
        $this->client->setPaymentMethod($this->business);
        $service = $this->createService(100.00);
        $invoice = $this->invoicer->generateAll($this->client)[0];
        $this->processor->payInvoice($invoice, $this->methodFactory->getStrategy($this->client->getPaymentMethod()));
        $feeCharged = floatval($service->meta()->where('key', 'ally_fee_charged')->first()->value);
        $this->assertAllyFeeRate(100, BillingCalculator::getBankAccountRate(), $feeCharged);
    }

    /** @test */
    function payment_processor_should_calculate_ally_fee_based_on_existing_overrides_for_private_pay()
    {
        $override = FeeOverrideRule::create([
            'business_id' => $this->business->id,
            'rate' => 0.02,
            'payment_method_type' => PaymentMethodType::CC(),
        ]);

        $service = $this->createService(123.68);
        $invoice = $this->invoicer->generateAll($this->client)[0];
        $this->client->setPaymentMethod(factory(CreditCard::class)->create());
        $this->processor->payInvoice($invoice, $this->methodFactory->getStrategy($this->client->getPaymentMethod()));

        $feeCharged = floatval($service->meta()->where('key', 'ally_fee_charged')->first()->value);
        $this->assertAllyFeeRate(123.68, $override->getRate(), $feeCharged);
    }

    /** @test */
    function payment_processor_should_calculate_ally_fee_based_on_existing_overrides_for_provider_pay()
    {
        $override = FeeOverrideRule::create([
            'business_id' => $this->business->id,
            'rate' => 0.06,
            'payment_method_type' => PaymentMethodType::ACH_P(),
        ]);

        $this->business->setBankAccount('paymentAccount', factory(BankAccount::class)->create(['user_id' => null, 'business_id' => $this->business->id]));
        $this->client->setPaymentMethod($this->business);

        $service = $this->createService(123.68);
        $invoice = $this->invoicer->generateAll($this->client)[0];
        $this->processor->payInvoice($invoice, $this->methodFactory->getStrategy($this->client->getPaymentMethod()));

        $feeCharged = floatval($service->meta()->where('key', 'ally_fee_charged')->first()->value);
        $this->assertAllyFeeRate(123.68, $override->getRate(), $feeCharged);
    }

    private function assertAllyFeeRate(float $paymentAmount, $expectedRate, $actualFee): void
    {
        $this->assertEquals(BillingCalculator::calculateAllyFee($paymentAmount, floatval($expectedRate), true), $actualFee);
    }
}