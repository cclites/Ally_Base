<?php


namespace Tests\Model;


use App\Billing\Deposit;
use App\Events\DepositFailed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepositTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function a_deposit_emits_a_domain_event_when_marked_failed()
    {
        \Event::fake();

        $deposit = factory(Deposit::class)->create();
        $deposit->markFailed();

        \Event::assertDispatched(DepositFailed::class);
        $this->assertFalse($deposit->success);
    }
}