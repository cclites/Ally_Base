<?php

namespace Tests\Feature;

use App\Billing\CaregiverInvoice;
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
        $invoice->deposits()->saveMany($deposits);

        $this->assertCount(2, $invoice->deposits,  'The invoice did not collect the deposits.');
        $this->assertCount(1, $deposits[0]->caregiverInvoices,  'The deposit did not relate to the caregiver invoice.');
    }
}
