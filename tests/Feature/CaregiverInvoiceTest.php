<?php

namespace Tests\Feature;

use App\Billing\CaregiverInvoice;
use App\Billing\CaregiverInvoiceItem;
use App\Billing\ClientInvoice;
use App\Billing\ClientInvoiceItem;
use App\Billing\Deposit;
use App\Billing\Generators\CaregiverInvoiceGenerator;
use App\Billing\Payment;
use App\Caregiver;
use App\Shift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class CaregiverInvoiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function an_invoice_can_be_generated_from_shifts()
    {
        $caregiver = factory(Caregiver::class)->create();
        $excludedShift = factory(Shift::class)->create();
        /** @var Shift $includedShift */
        $includedShift = factory(Shift::class)->create([
            'caregiver_id' => $caregiver->id,
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

        $generator = new CaregiverInvoiceGenerator();
        $invoice = $generator->generate($caregiver);

        $this->assertInstanceOf(CaregiverInvoice::class, $invoice);
        $this->assertEquals(20, $invoice->getAmount());
        $this->assertEquals(20, $invoice->getAmountDue());
    }

    /**
     * @test
     */
    function an_invoice_can_have_multiple_deposits()
    {
        /** @var CaregiverInvoice $invoice */
        $invoice = factory(CaregiverInvoice::class)->create();
        $deposits = factory(Deposit::class, 2)->create();
        $invoice->deposits()->saveMany($deposits, [['amount_applied' => 0], ['amount_applied' => 0]]);

        $this->assertCount(2, $invoice->deposits,  'The invoice did not collect the deposits.');
        $this->assertCount(1, $deposits[0]->caregiverInvoices,  'The deposit did not relate to the caregiver invoice.');
    }

    /**
     * @test
     */
    function the_invoice_amount_should_be_updated_when_adding_an_item()
    {
        $invoice = factory(CaregiverInvoice::class)->create();

        $this->assertEquals(0, $invoice->amount);

        $item = factory(CaregiverInvoiceItem::class)->make(['total' => 20.00]);
        $invoice->addItem($item);

        $this->assertEquals(20, $invoice->amount);
    }


    /**
     * @test
     */
    function the_amount_paid_should_be_updated_when_adding_a_deposit()
    {
        $invoice = factory(CaregiverInvoice::class)->create();

        $this->assertEquals(0, $invoice->amount_paid);

        $deposit = factory(Deposit::class)->make(['amount' => 20.00]);
        $invoice->addDeposit($deposit, 20);

        $this->assertEquals(20, $invoice->amount_paid);
    }
}
