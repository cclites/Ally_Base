<?php

namespace Tests\Feature;

use App\Billing\BusinessInvoice;
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
        $invoice->deposits()->saveMany($deposits);

        $this->assertCount(2, $invoice->deposits,  'The invoice did not collect the deposits.');
        $this->assertCount(1, $deposits[0]->businessInvoices,  'The deposit did not relate to the business invoice.');
    }
}
