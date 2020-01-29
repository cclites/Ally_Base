<?php
namespace Tests\Feature;

use App\Billing\ClientRate;
use App\Billing\Service;
use App\Billing\Validators\ClientRateValidator;
use App\Business;
use App\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientRateTest extends TestCase
{
    use RefreshDatabase;

    /** @var ClientRateValidator */
    private $validator;

    /** @var Client */
    private $client;

    protected function setUp() : void
    {
        parent::setUp();
        $this->validator = new ClientRateValidator();
        $business = factory(Business::class)->create(['timezone' => 'America/New_York']);
        $this->client = factory(Client::class)->create(['business_id' => $business->id]);
    }

    protected function makeRates(array $data = [], ?int $count = null)
    {
        return factory(ClientRate::class, $count)->make(['client_id' => $this->client->id] + $data);
    }

    /**
     * @test
     */
    function clients_can_have_rates()
    {
        $rates = $this->makeRates([], 2);
        $this->client->rates()->saveMany($rates);

        $this->assertEquals(2, $this->client->rates()->count());
    }

    /**
     * @test
     */
    function a_client_can_have_a_default_rate_by_setting_related_data_null()
    {
        // Create a non-default rate
        $service = factory(Service::class)->create();
        $otherRate = $this->makeRates(['service_id' => $service->id]);

        $defaultRate = $this->makeRates(['payer_id' => null, 'service_id' => null, 'caregiver_id' => null]);

        $this->client->rates()->saveMany([$otherRate, $defaultRate]);

        $this->assertEquals($defaultRate->id, $this->client->getDefaultRate()->id);
    }

    /**
     * @test
     */
    function rates_that_do_not_overlap_are_valid()
    {
        $rate1 = $this->makeRates(['payer_id' => null, 'service_id' => null, 'caregiver_id' => null, 'effective_start' => '2019-01-01', 'effective_end' => '2019-12-31']);
        $rate2 = $this->makeRates(['payer_id' => null, 'service_id' => null, 'caregiver_id' => null, 'effective_start' => '2020-01-01']);
        $rate3 = $this->makeRates(['effective_start' => '2019-01-01', 'effective_end' => '2019-12-31']);
        $rate4 = $this->makeRates(['payer_id' => $rate3->payer_id, 'service_id' => $rate3->service_id, 'caregiver_id' => $rate3->caregiver_id, 'effective_start' => '2020-01-01']);

        $this->client->rates()->saveMany([$rate1, $rate2, $rate3, $rate4]);

        $this->assertTrue($this->validator->validate($this->client), $this->validator->getErrorMessage());
    }

    /**
     * @test
     */
    function rates_that_do_overlap_are_invalid()
    {
        $rate1 = $this->makeRates(['payer_id' => null, 'service_id' => null, 'caregiver_id' => null, 'effective_start' => '2019-01-01', 'effective_end' => '2019-12-31']);
        $rate2 = $this->makeRates(['payer_id' => null, 'service_id' => null, 'caregiver_id' => null, 'effective_start' => '2019-06-30', 'effective_end' => '2019-12-31']);
        $rate3 = $this->makeRates(['effective_start' => '2019-01-01', 'effective_end' => '2019-12-31']);
        $rate4 = $this->makeRates(['payer_id' => $rate3->payer_id, 'service_id' => $rate3->service_id, 'caregiver_id' => $rate3->caregiver_id, 'effective_start' => '2019-12-31']);

        $this->client->rates()->saveMany([$rate1, $rate2]);

        $client2 = factory(Client::class)->create();
        $client2->rates()->saveMany([$rate3, $rate4]);

        $this->assertFalse($this->validator->validate($this->client));
        $this->assertFalse($this->validator->validate($client2));
    }
}