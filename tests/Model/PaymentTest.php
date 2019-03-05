<?php


namespace Tests\Model;


use App\Billing\Payment;
use App\Events\PaymentFailed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function a_payment_emits_a_domain_event_when_marked_failed()
    {
        \Event::fake();

        $payment = factory(Payment::class)->create();
        $payment->markFailed();

        \Event::assertDispatched(PaymentFailed::class);
        $this->assertFalse($payment->success);
    }
}