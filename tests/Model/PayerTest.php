<?php
namespace Tests\Model;

use App\Billing\Payer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    function a_payer_can_be_created()
    {
        $payer = factory(Payer::class)->create();

        $this->assertGreaterThan(0, $payer->id);
    }
}