<?php

namespace Tests\Feature;

use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Exceptions\PayerAllowanceExceeded;
use App\Billing\Generators\ClientInvoiceGenerator;
use App\Billing\Payer;
use App\Billing\Payment;
use App\Billing\Validators\ClientPayerValidator;
use App\Client;
use App\Shift;
use Tests\CreatesClientInvoiceResources;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class ClientInvoiceTest
 * Note:  This test uses ShiftService as the default invoiceable and makes use of ShiftAdjustment for a credit
 *
 * @package Tests\Feature
 */
class ClientInvoiceTest extends TestCase
{
    use RefreshDatabase;
    use CreatesClientInvoiceResources;

    /** @var \App\Client */
    private $client;

    /** @var ClientInvoiceGenerator */
    private $invoicer;

    protected function setUp() : void
    {
        parent::setUp();

        $this->client = factory(Client::class)->create();
        $this->invoicer = new ClientInvoiceGenerator(app(ClientPayerValidator::class));
    }

    /**
     * @test
     */
    function an_invoice_can_have_multiple_payments()
    {
        /** @var ClientInvoice $invoice */
        $invoice = factory(ClientInvoice::class)->create();
        $payments = factory(Payment::class, 2)->create();
        $invoice->payments()->saveMany($payments, [['amount_applied' => 0], ['amount_applied' => 0]]);

        $this->assertCount(2, $invoice->payments, 'The invoice did not collect the payments.');
        $this->assertCount(1, $payments[0]->invoices, 'The payment did not relate to the invoice.');
    }

    /**
     * @test
     */
    function services_on_previous_dates_should_not_be_billed_to_the_current_payer()
    {
        /*
         * Payer A has an effective end of December 31st.  Payer B has an effective start on January 1st.
         * A shift occurring on December 30th is invoiced on January 2nd,  the shift should be billed to Payer A, not Payer B.
         * A shift occurring on January 1st should be invoiced to Payer B, not Payer A.
         */

        $payerA = $this->createBalancePayer('2018-01-01', '2018-12-31');
        $payerB = $this->createBalancePayer('2019-01-01', '9999-12-31');
        $serviceA = $this->createService(50.0, '2018-12-30');
        $serviceB = $this->createService(60.0, '2019-01-01');

        $invoices = collect($this->invoicer->generateAll($this->client));
        $payerAInvoice = $invoices->where('client_payer_id', $payerA->id)->first();
        $payerBInvoice = $invoices->where('client_payer_id', $payerB->id)->first();

        $this->assertInstanceOf(ClientInvoice::class, $payerAInvoice);
        $this->assertEquals(50.0, $payerAInvoice->getAmountDue());
        $this->assertInstanceOf(ClientInvoice::class, $payerBInvoice);
        $this->assertEquals(60.0, $payerBInvoice->getAmountDue());
    }

    /**
     * @test
     */
    function allowance_payer_before_a_split_payer_does_not_skew_the_amounts()
    {
        /*
         * Payer A has an allowance of $50.00.  Shift A is $100.00 of services.
         * Payer B is a split payer of 50%, Payer C is the same.
         *
         * Payer A needs to pay $50.00, Payer B & C both need to pay $25.00.
         */

        $payerA = $this->createAllowancePayer(50.0);
        $payerB = $this->createSplitPayer(0.4);
        $payerC = $this->createSplitPayer(0.2);
        $payerD = $this->createSplitPayer(0.4);
        $this->createService(100.0);

        $invoices = collect($this->invoicer->generateAll($this->client));
        $payerAInvoice = $invoices->where('client_payer_id', $payerA->id)->first();
        $payerBInvoice = $invoices->where('client_payer_id', $payerB->id)->first();
        $payerCInvoice = $invoices->where('client_payer_id', $payerC->id)->first();
        $payerDInvoice = $invoices->where('client_payer_id', $payerD->id)->first();

        $this->assertEquals(50.0, $payerAInvoice->getAmountDue());
        $this->assertEquals(20.0, $payerBInvoice->getAmountDue());
        $this->assertEquals(10.0, $payerCInvoice->getAmountDue());
        $this->assertEquals(20.0, $payerDInvoice->getAmountDue());
    }

    /**
     * @test
     */
    function allowance_payer_accounts_for_credit_adjustments_before_throwing_exception()
    {
        /*
         * Allowance payer has a $100.00 allowance.
         * The shifts assigned to this payer total $150.00, but the Payer has $50.00 in credit adjustments.
         * The invoice should be successfully generated for $100.00 due, taking the credit into account before issuing a PayerAllowanceExceeded exception
         */

        $clientPayer = $this->createAllowancePayer(100.0);
        $this->createBalancePayer();

        $this->createService(150.00, '2019-01-15', $clientPayer->payer_id);
        $this->createCreditAdjustment(50.00, '2019-01-15', $clientPayer->payer_id);

        $invoices = collect($this->invoicer->generateAll($this->client));
        $clientPayerInvoice = $invoices->where('client_payer_id', $clientPayer->id)->first();

        $this->assertEquals(100.0, $clientPayerInvoice->getAmountDue());
        $this->assertCount(1, $invoices, 'Only one invoice should have been generated since the service was assigned to a payer.');
    }

    /**
     * @test
     */
    function services_assigned_to_a_payer_should_be_allocated_prior_to_auto()
    {
        /*
         * Payer A has a $100 allowance, shift A on 1st date is auto and $70, shift B on 2nd date is assigned to Payer A for $80.
         * Shift B should be allocated in full to Payer A, then Shift A should allocated for $20 (remaining allowance)
         * to Payer A and the balance to payer B.
         */

        $payerA = $this->createAllowancePayer(100.00);
        $payerB = $this->createBalancePayer();
        $serviceA = $this->createService(70.00, '2019-01-15');
        $serviceB = $this->createService(80.00, '2019-01-17', $payerA->payer_id);

        $invoices = collect($this->invoicer->generateAll($this->client));
        $payerAInvoice = $invoices->where('client_payer_id', $payerA->id)->first();
        $itemB = $payerAInvoice->items->where('invoiceable_id', $serviceB->id)->first();
        $itemA = $payerAInvoice->items->where('invoiceable_id', $serviceA->id)->first();

        $this->assertEquals(80, $itemB->amount_due);
        $this->assertEquals(20, $itemA->amount_due);
    }

    /**
     * @test
     */
    function services_assigned_to_a_payer_that_exceeds_allowance_should_throw_exception()
    {
        /*
         * Payer A has a $50 allowance,  Shift A and B are both $30 and assigned to Payer A,
         * shift B should cause a PayerAllowanceExceeded exception
         */
        $this->expectException(PayerAllowanceExceeded::class);

        $payerA = $this->createAllowancePayer(50.00);
        $payerB = $this->createBalancePayer();

        $serviceA = $this->createService(30.00, '2019-01-15', $payerA->payer_id);
        $serviceB = $this->createService(30.00, '2019-01-17', $payerA->payer_id);

        $invoices = $this->invoicer->generateAll($this->client);
        dump(null);
    }

    /**
     * @test
     */
    function expenses_assigned_to_split_payers_should_calculate_fees_independently()
    {
        /**
         * Payer A/B has a 80/20 split.  A shift expense of $100 is assigned.
         * Payer A default payment method is ACH (3% fee), Payer B's default payment method is CC (5% fee)
         * Payer A should pay $82.40, Payer B should pay $21.00
         */

        $payerA = $this->createSplitPayer(0.8);
        $payerB = $this->createSplitPayer(0.2);
        $payerB->update(['payer_id' => Payer::PRIVATE_PAY_ID]);
        $this->client->setPaymentMethod($this->createCreditCard());
        $shift = $this->createShiftWithExpense(100);

        $invoices = $this->invoicer->generateAll($this->client);
        $payerAInvoice = collect($invoices)->where('client_payer_id', $payerA->id)->first();
        $payerBInvoice = collect($invoices)->where('client_payer_id', $payerB->id)->first();
        $shiftExpense = $payerAInvoice->items[0]->invoiceable;

        $this->assertEquals(82.40, $payerAInvoice->getAmount());
        $this->assertEquals(21.00, $payerBInvoice->getAmount());
        $this->assertEquals(3.40, $shiftExpense->ally_fee, 'The ally fee was not correctly updated on the invoiceable.');
    }

    /**
     * @test
     */
    function expenses_assigned_to_allowance_payers_should_calculate_fees_independently()
    {
        /**
         * Payer A is an allowance payer of $80, Payer B is a balance payer.  A shift expense of $100 is assigned.
         * Payer A default payment method is ACH (3% fee), Payer B's default payment method is CC (5% fee)
         * Payer A should pay $80.00 ($77.67 towards expense), Payer B should pay $23.45 ($22.33 towards expense)
         */

        $payerA = $this->createAllowancePayer(80.00);
        $payerB = $this->createBalancePayer();
        $payerB->update(['payer_id' => Payer::PRIVATE_PAY_ID]);
        $this->client->setPaymentMethod($this->createCreditCard());
        $shift = $this->createShiftWithExpense(100);

        $invoices = $this->invoicer->generateAll($this->client);
        $payerAInvoice = collect($invoices)->where('client_payer_id', $payerA->id)->first();
        $payerBInvoice = collect($invoices)->where('client_payer_id', $payerB->id)->first();
        $shiftExpense = $payerAInvoice->items[0]->invoiceable;

        $this->assertEquals(80.00, $payerAInvoice->getAmount());
        $this->assertEquals(23.45, $payerBInvoice->getAmount());
        $this->assertEquals(3.45, $shiftExpense->ally_fee, 'The ally fee was not correctly updated on the invoiceable.');
    }

    /**
     * @test
     */
    function expenses_with_small_incremental_rates_dont_create_differences_between_due_and_total()
    {
        /**
         * A mileage rate of $0.535 when multiplied by units and rounded to two decimal places does not
         * end up with differing amounts for the total and the amount due
         */

        $payerA = $this->createBalancePayer();
        $payerA->update(['payer_id' => Payer::PRIVATE_PAY_ID]);
        $this->client->setPaymentMethod($this->createCreditCard());
        $shift = $this->createShiftWithMileage(0.535, 36);

        $invoice = $this->invoicer->generateAll($this->client)[0];
        $item = $invoice->items->first();

        $this->assertEquals(0.5618, $item->rate);
        $this->assertEquals(20.22, $item->amount_due);
        $this->assertEquals(20.22, $item->total);
    }


    /**
     * @test
     */
    function the_invoice_amount_should_be_updated_when_adding_an_item()
    {
        $invoice = factory(ClientInvoice::class)->create();

        $this->assertEquals(0, $invoice->amount);

        $item = factory(ClientInvoiceItem::class)->make(['amount_due' => 20.00]);
        $invoice->addItem($item);

        $this->assertEquals(20, $invoice->amount);
    }


    /**
     * @test
     */
    function the_amount_paid_should_be_updated_when_adding_a_payment()
    {
        $invoice = factory(ClientInvoice::class)->create();

        $this->assertEquals(0, $invoice->amount_paid);

        $payment = factory(Payment::class)->make(['amount' => 20.00]);
        $invoice->addPayment($payment, 20);

        $this->assertEquals(20, $invoice->amount_paid);
    }

    /**
     * @test
     */
    function the_amount_paid_should_be_reduce_when_a_payment_is_marked_as_failed()
    {
        $invoice = factory(ClientInvoice::class)->create();

        $this->assertEquals(0, $invoice->amount_paid);

        $payment = factory(Payment::class)->make(['amount' => 20.00]);
        $invoice->addPayment($payment, 20);

        $this->assertEquals(20, $invoice->amount_paid);

        $payment->markFailed();

        $this->assertEquals(0, $invoice->fresh()->amount_paid);
    }

    /**
     * @test
     */
    function shifts_should_be_waiting_for_charge_after_invoicing()
    {
        $payerA = $this->createAllowancePayer(20.00);
        $payerB = $this->createBalancePayer();
        $service = $this->createService(50.00);
        $shift = $service->shift;

        $this->assertEquals(Shift::WAITING_FOR_INVOICE, $shift->status);
        $this->invoicer->generateAll($this->client);

        $this->assertEquals(Shift::WAITING_FOR_CHARGE, $shift->fresh()->status);
    }
}
