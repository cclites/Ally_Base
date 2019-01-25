<?php

namespace Tests\Feature;

use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\ClientPayer;
use App\Billing\Contracts\InvoiceableInterface;
use App\Billing\Exceptions\PayerAllowanceExceeded;
use App\Billing\Generators\ClientInvoiceGenerator;
use App\Billing\Invoiceable\ShiftAdjustment;
use App\Billing\Invoiceable\ShiftService;
use App\Billing\Payer;
use App\Billing\Payment;
use App\Billing\PaymentMethods\CreditCard;
use App\Billing\Validators\ClientPayerValidator;
use App\Client;
use App\Shift;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
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

    /** @var \App\Client */
    private $client;

    /** @var ClientInvoiceGenerator */
    private $invoicer;

    protected function setUp()
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

        $this->assertCount(2, $invoice->payments,  'The invoice did not collect the payments.');
        $this->assertCount(1, $payments[0]->invoices,  'The payment did not relate to the invoice.');
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
        $payerAInvoice = $invoices->where('payer_id', $payerA->payer_id)->first();
        $payerBInvoice = $invoices->where('payer_id', $payerB->payer_id)->first();

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
        $payerAInvoice = $invoices->where('payer_id', $payerA->payer_id)->first();
        $payerBInvoice = $invoices->where('payer_id', $payerB->payer_id)->first();
        $payerCInvoice = $invoices->where('payer_id', $payerC->payer_id)->first();
        $payerDInvoice = $invoices->where('payer_id', $payerD->payer_id)->first();

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

        $payer = $this->createAllowancePayer(100.0);
        $this->createBalancePayer();

        $this->createService(150.00, '2019-01-15', $payer->payer_id);
        $this->createCreditAdjustment(50.00, '2019-01-15', $payer->payer_id);

        $invoices = collect($this->invoicer->generateAll($this->client));
        $payerInvoice = $invoices->where('payer_id', $payer->payer_id)->first();

        $this->assertEquals(100.0, $payerInvoice->getAmountDue());
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
        $payerAInvoice = $invoices->where('payer_id', $payerA->payer_id)->first();
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

        $this->invoicer->generateAll($this->client);
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
        $this->client->setPaymentMethod(factory(CreditCard::class)->create());
        $shift = $this->createShiftWithExpense(100);

        $invoices = $this->invoicer->generateAll($this->client);
        $payerAInvoice = collect($invoices)->where('payer_id', $payerA->payer_id)->first();
        $payerBInvoice = collect($invoices)->where('payer_id', $payerB->payer_id)->first();
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
        $this->client->setPaymentMethod(factory(CreditCard::class)->create());
        $shift = $this->createShiftWithExpense(100);

        $invoices = $this->invoicer->generateAll($this->client);
        $payerAInvoice = collect($invoices)->where('payer_id', $payerA->payer_id)->first();
        $payerBInvoice = collect($invoices)->where('payer_id', $payerB->payer_id)->first();
        $shiftExpense = $payerAInvoice->items[0]->invoiceable;

        $this->assertEquals(80.00, $payerAInvoice->getAmount());
        $this->assertEquals(23.45, $payerBInvoice->getAmount());
        $this->assertEquals(3.45, $shiftExpense->ally_fee, 'The ally fee was not correctly updated on the invoiceable.');
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


    ////////////////////////////////////
    //// Private Methods
    ////////////////////////////////////

    private function createAllowancePayer(float $allowance, string $effective_start = '2019-01-01', string $effective_end = '9999-12-31',
        string $allocation_type = ClientPayer::ALLOCATION_MONTHLY): ClientPayer
    {
        $payer = factory(Payer::class)->create();
        $clientPayer = new ClientPayer([
            'payer_id' => $payer->id,
            'effective_start' => $effective_start,
            'effective_end' => $effective_end,
            'payment_allocation' => $allocation_type,
            'payment_allowance' => $allowance
        ]);

        $this->client->payers()->save($clientPayer);
        return $clientPayer;
    }

    private function createSplitPayer(float $splitPercentage, string $effective_start = '2019-01-01', string $effective_end = '9999-12-31'): ClientPayer
    {
        $payer = factory(Payer::class)->create();
        $clientPayer = new ClientPayer([
            'payer_id' => $payer->id,
            'effective_start' => $effective_start,
            'effective_end' => $effective_end,
            'payment_allocation' => ClientPayer::ALLOCATION_SPLIT,
            'split_percentage' => $splitPercentage
        ]);

        $this->client->payers()->save($clientPayer);
        return $clientPayer;
    }

    private function createBalancePayer(string $effective_start = '2019-01-01', string $effective_end = '9999-12-31'): ClientPayer
    {
        $payer = factory(Payer::class)->create();
        $clientPayer = new ClientPayer([
            'payer_id' => $payer->id,
            'effective_start' => $effective_start,
            'effective_end' => $effective_end,
            'payment_allocation' => ClientPayer::ALLOCATION_BALANCE,
        ]);

        $this->client->payers()->save($clientPayer);
        return $clientPayer;
    }

    private function createService(float $amount, string $date = '2019-01-15', ?int $payerId = null): InvoiceableInterface
    {
        $shift = factory(Shift::class)->create([
            'client_id' => $this->client->id,
            'payer_id' => null,
            'service_id' => null,
            'checked_in_time' => $date . ' 12:00:00',
            'status' => Shift::WAITING_FOR_INVOICE,
        ]);

        $shiftService = factory(ShiftService::class)->create([
            'shift_id' => $shift->id,
            'payer_id' => $payerId,
            'duration' => 1,
            'client_rate' => $amount,
            'caregiver_rate' => round($amount * .75, 2),
            'ally_rate' => null,
        ]);

        return $shiftService;
    }

    private function createShiftWithExpense(float $amount, string $date = '2019-01-15', ?int $payerId = null): InvoiceableInterface
    {
        $shift = factory(Shift::class)->create([
            'client_id' => $this->client->id,
            'payer_id' => $payerId,
            'service_id' => null,
            'caregiver_rate' => 0,
            'client_rate' => 0,
            'other_expenses' => $amount,
            'checked_in_time' => $date . ' 12:00:00',
            'status' => Shift::WAITING_FOR_INVOICE,
        ]);

        return $shift;
    }

    private function createCreditAdjustment(float $amount, string $date = '2019-01-15', ?int $payerId = null)
    {
        $adjustment = factory(ShiftAdjustment::class)->create([
            'client_id' => $this->client->id,
            'client_rate' => -$amount,
            'units' => 1,
            'payer_id' => $payerId,
            'status' => 'WAITING_FOR_INVOICE',
            'created_at' => $date . ' 00:00:00',
            'updated_at' => $date . ' 00:00:00',
        ]);

        return $adjustment;
    }
}
