<?php
namespace Tests\Feature;

use App\Billing\Payer;
use App\Billing\PayerRate;
use App\Billing\Validators\PayerRateValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayerRateTest extends TestCase
{
    use RefreshDatabase;

    /** @var PayerRateValidator */
    private $validator;

    protected function setUp() : void
    {
        parent::setUp();
        $this->validator = new PayerRateValidator;
    }

    /**
     * @test
     */
    function payers_can_have_rates()
    {
        $rates = factory(PayerRate::class, 2)->make(['payer_id' => null]);
        $payer = factory(Payer::class)->create();
        $payer->rates()->saveMany($rates);

        $this->assertEquals(2, $payer->rates()->count());
    }

    /**
     * @test
     */
    function a_payer_can_have_a_default_rate_by_setting_service_null()
    {
        $otherRate = factory(PayerRate::class)->make(['payer_id' => null]);
        $defaultRate = factory(PayerRate::class)->make(['payer_id' => null, 'service_id' => null]);
        $payer = factory(Payer::class)->create();
        $payer->rates()->save($otherRate);
        $payer->rates()->save($defaultRate);

        $this->assertEquals($defaultRate->id, $payer->getDefaultRate()->id);
    }

    /**
     * @test
     */
    function rates_that_do_not_overlap_are_valid()
    {
        $rate1 = factory(PayerRate::class)->make(['payer_id' => null, 'service_id' => null, 'effective_start' => '2019-01-01', 'effective_end' => '2019-12-31']);
        $rate2 = factory(PayerRate::class)->make(['payer_id' => null, 'service_id' => null, 'effective_start' => '2020-01-01']);
        $rate3 = factory(PayerRate::class)->make(['effective_start' => '2019-01-01', 'effective_end' => '2019-12-31']);
        $rate4 = factory(PayerRate::class)->make(['payer_id' => $rate3->payer_id, 'service_id' => $rate3->service_id, 'effective_start' => '2020-01-01']);

        $payer = factory(Payer::class)->create();
        $payer->rates()->saveMany([$rate1, $rate2, $rate3, $rate4]);

        $this->assertTrue($this->validator->validate($payer), $this->validator->getErrorMessage());
    }

    /**
     * @test
     */
    function rates_that_do_overlap_are_invalid()
    {
        $rate1 = factory(PayerRate::class)->make(['payer_id' => null, 'service_id' => null, 'effective_start' => '2019-01-01', 'effective_end' => '2019-12-31']);
        $rate2 = factory(PayerRate::class)->make(['payer_id' => null, 'service_id' => null, 'effective_start' => '2019-06-30', 'effective_end' => '2019-12-31']);
        $rate3 = factory(PayerRate::class)->make(['effective_start' => '2019-01-01', 'effective_end' => '2019-12-31']);
        $rate4 = factory(PayerRate::class)->make(['payer_id' => $rate3->payer_id, 'service_id' => $rate3->service_id, 'effective_start' => '2019-12-31']);

        $payer = factory(Payer::class)->create();
        $payer->rates()->saveMany([$rate1, $rate2]);

        $payer2 = factory(Payer::class)->create();
        $payer2->rates()->saveMany([$rate3, $rate4]);

        $this->assertFalse($this->validator->validate($payer));
        $this->assertFalse($this->validator->validate($payer2));
    }
}