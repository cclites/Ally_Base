<?php

namespace Tests\Feature;

use App\Billing\CaregiverInvoice;
use App\Billing\CaregiverInvoiceItem;
use App\Billing\Deposit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class CaregiverInvoiceTest extends TestCase
{
    use RefreshDatabase;

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
