<?php

namespace Tests\Unit;

use App\Shifts\Rates;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RatesTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_client_rate_is_calculated_as_sum_of_caregiver_and_provider()
    {
        $rates = new Rates(2.50, 1.25);

        $this->assertSame($rates->getClientRate(), 3.75);
    }

    public function test_client_rate_can_be_set_to_influence_provider_fee()
    {
        $rates = new Rates(2.50);
        $rates->setClientRate(3.75);

        $this->assertSame($rates->getClientRate(), 3.75);
        $this->assertSame($rates->provider_fee, 1.25);
    }

}
