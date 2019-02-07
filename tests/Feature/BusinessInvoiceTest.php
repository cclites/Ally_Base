<?php

namespace Tests\Feature;

use App\Billing\BusinessInvoice;
use App\Billing\BusinessInvoiceItem;
use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Deposit;
use App\Billing\Generators\BusinessInvoiceGenerator;
use App\Billing\Payment;
use App\Business;
use App\Shift;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BusinessInvoiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function an_invoice_can_be_generated_from_shifts()
    {
        $business = factory(Business::class)->create(['timezone' => 'UTC']);
        $excludedShift = factory(Shift::class)->create();
        /** @var Shift $includedShift */
        $includedShift = factory(Shift::class)->create([
            'business_id' => $business->id,
            'client_rate' => 15,
            'caregiver_rate' => 10,
            'checked_in_time' => '2019-01-01 12:00:00',
            'checked_out_time' => '2019-01-01 14:00:00',
        ]);

        /** @var ClientInvoice $clientInvoice */
        $clientInvoice = factory(ClientInvoice::class)->create(['amount' => 0, 'amount_paid' => 0]);
        /** @var ClientInvoiceItem $clientInvoiceItem */
        $clientInvoiceItem = factory(ClientInvoiceItem::class)->make([
            'rate' => 15,
            'units' => 2,
            'total' => 30,
            'amount_due' => 30,
        ]);
        $clientInvoiceItem->associateInvoiceable($includedShift);
        $clientInvoice->addItem($clientInvoiceItem);
        $clientInvoice->update(['amount_paid' => 30, 'amount' => 30]);
        $includedShift->addAmountCharged(new Payment(), 30, 1.50);

        $generator = new BusinessInvoiceGenerator();
        $invoice = $generator->generate($business);

        $this->assertInstanceOf(BusinessInvoice::class, $invoice);
        $this->assertEquals(8.50, $invoice->getAmount());
        $this->assertEquals(8.50, $invoice->getAmountDue());
    }

    /**
     * @test
     */
    function an_invoice_can_have_multiple_deposits()
    {
        /** @var \App\Billing\BusinessInvoice $invoice */
        $invoice = factory(BusinessInvoice::class)->create();
        $deposits = factory(Deposit::class, 2)->create();
        $invoice->deposits()->saveMany($deposits, [['amount_applied' => 0], ['amount_applied' => 0]]);

        $this->assertCount(2, $invoice->deposits,  'The invoice did not collect the deposits.');
        $this->assertCount(1, $deposits[0]->businessInvoices,  'The deposit did not relate to the business invoice.');
    }

    /**
     * @test
     */
    function the_invoice_amount_should_be_updated_when_adding_an_item()
    {
        $invoice = factory(BusinessInvoice::class)->create();

        $this->assertEquals(0, $invoice->amount);

        $item = factory(BusinessInvoiceItem::class)->make(['total' => 20.00]);
        $invoice->addItem($item);

        $this->assertEquals(20, $invoice->amount);
    }


    /**
     * @test
     */
    function the_amount_paid_should_be_updated_when_adding_a_deposit()
    {
        $invoice = factory(BusinessInvoice::class)->create();

        $this->assertEquals(0, $invoice->amount_paid);

        $deposit = factory(Deposit::class)->make(['amount' => 20.00]);
        $invoice->addDeposit($deposit, 20);

        $this->assertEquals(20, $invoice->amount_paid);
    }
}
