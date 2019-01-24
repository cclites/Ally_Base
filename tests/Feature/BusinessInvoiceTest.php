<?php

namespace Tests\Feature;

use App\Billing\BusinessInvoice;
use App\Billing\BusinessInvoiceItem;
use App\Billing\Deposit;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BusinessInvoiceTest extends TestCase
{
    use RefreshDatabase;

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
