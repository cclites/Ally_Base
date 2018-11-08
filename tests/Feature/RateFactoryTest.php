<?php

namespace Tests\Feature;

use App\Business;
use App\Businesses\Settings;
use App\Caregiver;
use App\Client;
use App\RateCode;
use App\Shifts\RateFactory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RateFactoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Client
     */
    public $client;

    /**
     * @var Caregiver
     */
    public $caregiver;

    /**
     * @var Business
     */
    public $business;

    /**
     * @var Settings
     */
    public $settings;

    /**
     * @var RateFactory
     */
    public $rateFactory;

    function setUp()
    {
        parent::setUp();

        $this->business = factory(Business::class)->create();
        $this->client = factory(Client::class)->create(['business_id' => $this->business->id]);
        $this->caregiver = factory(Caregiver::class)->create();
        $this->settings = app(Settings::class);
        $this->rateFactory = app(RateFactory::class);
    }

    protected function attachCaregiver(array $pivot, $client = null, $caregiver = null)
    {
        if (!$caregiver) $caregiver = $this->caregiver;
        if (!$client) $client = $this->client;
        $client->caregivers()->attach($this->caregiver, $pivot);
    }

    protected function setSettings(array $settings)
    {
        $this->settings->set($this->business, $settings);
    }

    protected function createRateCode($rate, $type = 'client', $fixed = false)
    {
        return factory(RateCode::class)->create([
            'business_id' => $this->business->id,
            'rate' => $rate,
            'type' => $type,
            'fixed' => $fixed
        ]);
    }

    public function test_client_caregiver_hourly_free_text_rate()
    {
        $this->setSettings(['use_rate_codes' => 0]);
        $this->attachCaregiver([
            'caregiver_hourly_rate' =>  10,
            'provider_hourly_fee' => 5,
        ]);

        $rates = $this->rateFactory->getRatesForClientCaregiver($this->client, $this->caregiver, false);
        $this->assertEquals(10, $rates->caregiver_rate);
        $this->assertEquals(5, $rates->provider_fee);
        $this->assertEquals(15, $rates->client_rate);
        $this->assertEquals(false, $rates->fixed_rates);
    }

    public function test_client_caregiver_fixed_free_text_rate()
    {
        $this->setSettings(['use_rate_codes' => 0]);
        $this->attachCaregiver([
            'caregiver_fixed_rate' =>  100,
            'provider_fixed_fee' => 50,
        ]);

        $rates = $this->rateFactory->getRatesForClientCaregiver($this->client, $this->caregiver, true);
        $this->assertEquals(100, $rates->caregiver_rate);
        $this->assertEquals(50, $rates->provider_fee);
        $this->assertEquals(150, $rates->client_rate);
        $this->assertEquals(true, $rates->fixed_rates);
    }

    public function test_client_caregiver_hourly_rate_code()
    {
        $this->setSettings(['use_rate_codes' => 1, 'rate_structure' => 'client_rate']);
        $hourlyClRateCode = $this->createRateCode(18, 'client');
        $hourlyCgRateCode = $this->createRateCode(12, 'caregiver');

        $this->attachCaregiver([
            'caregiver_hourly_id' =>  $hourlyCgRateCode->id,
            'client_hourly_id' => $hourlyClRateCode->id,
        ]);

        $rates = $this->rateFactory->getRatesForClientCaregiver($this->client, $this->caregiver, false);
        $this->assertEquals(12, $rates->caregiver_rate);
        $this->assertEquals(18, $rates->client_rate);
        $this->assertEquals(false, $rates->fixed_rates);
    }

    public function test_client_caregiver_fixed_rate_code()
    {
        $this->setSettings(['use_rate_codes' => 1, 'rate_structure' => 'client_rate']);
        $fixedClRateCode = $this->createRateCode(120, 'client', true);
        $fixedCgRateCode = $this->createRateCode(80, 'caregiver', true);


        $this->attachCaregiver([
            'caregiver_fixed_id' =>  $fixedCgRateCode->id,
            'client_fixed_id' => $fixedClRateCode->id,
        ]);

        $rates = $this->rateFactory->getRatesForClientCaregiver($this->client, $this->caregiver, true);
        $this->assertEquals(80, $rates->caregiver_rate);
        $this->assertEquals(120, $rates->client_rate);
        $this->assertEquals(true, $rates->fixed_rates);
    }

    public function test_client_default_rate_codes()
    {
        $this->setSettings(['use_rate_codes' => 1, 'rate_structure' => 'client_rate']);
        $hourlyClRateCode = $this->createRateCode(18, 'client');
        $fixedClRateCode = $this->createRateCode(120, 'client', true);

        $this->client->setDefaultHourlyRate($hourlyClRateCode);
        $this->client->setDefaultFixedRate($fixedClRateCode);

        $rates = $this->rateFactory->getRatesForClientCaregiver($this->client, $this->caregiver, true);
        $this->assertEquals(120, $rates->client_rate);
        $this->assertEquals(true, $rates->fixed_rates);

        $rates = $this->rateFactory->getRatesForClientCaregiver($this->client, $this->caregiver, false);
        $this->assertEquals(18, $rates->client_rate);
        $this->assertEquals(false, $rates->fixed_rates);
    }

    public function test_caregiver_default_rate_codes()
    {
        $this->setSettings(['use_rate_codes' => 1, 'rate_structure' => 'client_rate']);
        $hourlyClRateCode = $this->createRateCode(12, 'caregiver');
        $fixedClRateCode = $this->createRateCode(80, 'caregiver', true);

        $this->caregiver->setDefaultHourlyRate($hourlyClRateCode);
        $this->caregiver->setDefaultFixedRate($fixedClRateCode);

        $rates = $this->rateFactory->getRatesForClientCaregiver($this->client, $this->caregiver, true);
        $this->assertEquals(80, $rates->caregiver_rate);
        $this->assertEquals(true, $rates->fixed_rates);

        $rates = $this->rateFactory->getRatesForClientCaregiver($this->client, $this->caregiver, false);
        $this->assertEquals(12, $rates->caregiver_rate);
        $this->assertEquals(false, $rates->fixed_rates);
    }

    public function test_default_setting_adds_ally_fee_to_total_rate()
    {
        $this->setSettings(['use_rate_codes' => 1, 'rate_structure' => 'client_rate', 'include_ally_fee' => 0]);


        $hourlyClRateCode = $this->createRateCode($clientRate = 18, 'client');
        $this->client->setDefaultHourlyRate($hourlyClRateCode);

        $hourlyCgRateCode = $this->createRateCode($cgRate = 12, 'caregiver');
        $this->caregiver->setDefaultHourlyRate($hourlyCgRateCode);

        $rates = $this->rateFactory->getRatesForClientCaregiver($this->client, $this->caregiver, false);

        $this->assertGreaterThan(0, $rates->ally_fee);
        $this->assertEquals($clientRate + $rates->ally_fee, $rates->total_rate);
        $this->assertEquals($clientRate - $cgRate, $rates->provider_fee);
    }

    public function test_include_ally_fee_setting_deducts_from_provider_fee()
    {
        $this->setSettings(['use_rate_codes' => 1, 'rate_structure' => 'client_rate', 'include_ally_fee' => 1]);


        $hourlyClRateCode = $this->createRateCode($clientRate = 18, 'client');
        $this->client->setDefaultHourlyRate($hourlyClRateCode);

        $hourlyCgRateCode = $this->createRateCode($cgRate = 12, 'caregiver');
        $this->caregiver->setDefaultHourlyRate($hourlyCgRateCode);

        $rates = $this->rateFactory->getRatesForClientCaregiver($this->client, $this->caregiver, false);

        $this->assertGreaterThan(0, $rates->ally_fee);
        $this->assertEquals($clientRate, $rates->total_rate);
        $this->assertEquals($clientRate - $cgRate - $rates->ally_fee, $rates->provider_fee);
    }
}
