<?php

namespace Tests\Feature;

use App\Billing\Payment;
use App\Billing\Queries\PaymentQuery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentQueryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var PaymentQuery
     */
    private $paymentQuery;

    protected function setUp() : void
    {
        parent::setUp();
        $this->paymentQuery = new PaymentQuery();
    }

    /**
     * @test
     */
    function has_available_amounts_should_only_return_payments_with_unapplied_amounts()
    {
        $payment1 = factory(Payment::class)->create(['amount' => 20.00]);
        $payment2 = factory(Payment::class)->create(['amount' => 30.00]);
        \DB::table('invoice_payments')->insert(['invoice_id' => 1, 'payment_id' => $payment1->id, 'amount_applied' => 20]);

        $results = $this->paymentQuery->hasAmountAvailable()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($payment2->is($results[0]));
    }


    /**
     * @test
     */
    function has_available_amounts_should_only_successful_payments()
    {
        $payment1 = factory(Payment::class)->create(['amount' => 20.00]);
        $payment2 = factory(Payment::class)->create(['amount' => 30.00, 'success' => false]);

        $results = $this->paymentQuery->hasAmountAvailable()->get();
        $this->assertCount(1, $results);
    }
}
