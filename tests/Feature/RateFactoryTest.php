<?php

namespace Tests\Feature;

use App\Billing\BillingCalculator;
use App\Billing\Payments\Methods\BankAccount;
use App\Business;
use App\Businesses\SettingsRepository;
use App\Caregiver;
use App\Client;
use App\RateCode;
use App\Schedule;
use App\Shifts\RateFactory;
use Carbon\Carbon;
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
     * @var SettingsRepository
     */
    public $settings;

    /**
     * @var RateFactory
     */
    public $rateFactory;

    function setUp() : void
    {
        parent::setUp();

        $this->business = factory(Business::class)->create();
        $this->client = factory(Client::class)->create(['business_id' => $this->business->id]);
        $this->caregiver = factory(Caregiver::class)->create();
        $this->settings = app(SettingsRepository::class);
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

    /**
     * @todo This test needs to be updated for the new billing system, if applicable
     */
    public function client_caregiver_hourly_free_text_rate()
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

    /**
     * @todo This test needs to be updated for the new billing system, if applicable
     */
    public function client_caregiver_fixed_free_text_rate()
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

    /**
     * @todo This test needs to be updated for the new billing system, if applicable
     */
    public function default_setting_adds_ally_fee_to_total_rate()
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

    /**
     * @todo This test needs to be updated for the new billing system, if applicable
     */
    public function client_rate_cannot_be_less_than_caregiver_rate()
    {
        $this->setSettings(['use_rate_codes' => 1, 'rate_structure' => 'client_rate', 'include_ally_fee' => 0]);

        $hourlyClRateCode = $this->createRateCode($clientRate = 15, 'client');
        $this->client->setDefaultHourlyRate($hourlyClRateCode);

        $hourlyCgRateCode = $this->createRateCode($cgRate = 20, 'caregiver');
        $this->caregiver->setDefaultHourlyRate($hourlyCgRateCode);

        $rates = $this->rateFactory->getRatesForClientCaregiver($this->client, $this->caregiver, false);

        // Expect the client rate to be equal to the caregiver rate
        $this->assertEquals(20, $rates->client_rate);
        $this->assertGreaterThan(20, $rates->total_rate);
    }

    /**
     * @todo This test needs to be updated for the new billing system, if applicable
     */
    public function client_rate_less_than_cg_rate_uses_cg_rate_plus_ally_fees()
    {
        // this assumes include_ally_fee = 1  (Ally fees are included in the client rate)
        $this->setSettings(['use_rate_codes' => 1, 'rate_structure' => 'client_rate', 'include_ally_fee' => 1]);

        $hourlyClRateCode = $this->createRateCode($clientRate = 15, 'client');
        $this->client->setDefaultHourlyRate($hourlyClRateCode);

        $hourlyCgRateCode = $this->createRateCode($cgRate = 20, 'caregiver');
        $this->caregiver->setDefaultHourlyRate($hourlyCgRateCode);

        $rates = $this->rateFactory->getRatesForClientCaregiver($this->client, $this->caregiver, false);

        $expectedRate = $cgRate + ($cgRate * BillingCalculator::getCreditCardRate());
        $this->assertEquals($expectedRate, $rates->client_rate);
        $this->assertEquals($expectedRate, $rates->total_rate);
    }

    /**
     * @todo This test needs to be updated for the new billing system, if applicable
     */
    public function client_rate_is_adjusted_for_fee_if_caregiver_rate_is_equal()
    {
        // This covers the case where the ally fee is included but the caregiver rate is equal to, or close to, the client rate
        // It should adjust the client rate up to cover the caregiver rate and the ally fee.
        $this->setSettings(['use_rate_codes' => 1, 'rate_structure' => 'client_rate', 'include_ally_fee' => 1]);

        $hourlyClRateCode = $this->createRateCode($clientRate = 20, 'client');
        $this->client->setDefaultHourlyRate($hourlyClRateCode);

        $hourlyCgRateCode = $this->createRateCode($cgRate = 20, 'caregiver');
        $this->caregiver->setDefaultHourlyRate($hourlyCgRateCode);

        $rates = $this->rateFactory->getRatesForClientCaregiver($this->client, $this->caregiver, false);

        $expectedRate = $clientRate + ($clientRate * BillingCalculator::getCreditCardRate());
        $this->assertEquals($expectedRate, $rates->client_rate);
        $this->assertEquals($expectedRate, $rates->total_rate);
    }

    public function test_client_rate_is_pulled_from_default_codes_on_schedules()
    {
        $this->setSettings(['use_rate_codes' => 1, 'rate_structure' => 'client_rate']);

        $hourlyClRateCode = $this->createRateCode($clientRate = 20, 'client');
        $fixedClRateCode = $this->createRateCode($clientRate * 4, 'client', true);
        $this->client->setDefaultHourlyRate($hourlyClRateCode);
        $this->client->setDefaultFixedRate($fixedClRateCode);

        $hourlyCgRateCode = $this->createRateCode($cgRate = 12, 'caregiver');
        $fixedCgRateCode = $this->createRateCode($cgRate * 4, 'caregiver', true);
        $this->caregiver->setDefaultHourlyRate($hourlyCgRateCode);
        $this->caregiver->setDefaultFixedRate($fixedCgRateCode);

        $hourlySchedule = $this->makeSchedule([]);
        $fixedSchedule = $this->makeSchedule(['fixed_rates' => true]);

        $hourlyRates = $this->rateFactory->getRatesForSchedule($hourlySchedule);
        $fixedRates = $this->rateFactory->getRatesForSchedule($fixedSchedule);

        $this->assertEquals($clientRate, $hourlyRates->client_rate);
        $this->assertEquals($cgRate, $hourlyRates->caregiver_rate);
        $this->assertEquals($clientRate * 4, $fixedRates->client_rate);
        $this->assertEquals($cgRate * 4, $fixedRates->caregiver_rate);
    }

    public function test_client_rate_is_pulled_from_client_caregiver_assignment_on_schedules()
    {
        $this->setSettings(['use_rate_codes' => 1, 'rate_structure' => 'client_rate']);

        $hourlyClRateCode = $this->createRateCode($clientRate = 20, 'client');
        $fixedClRateCode = $this->createRateCode($clientRate * 4, 'client', true);
        $hourlyCgRateCode = $this->createRateCode($cgRate = 12, 'caregiver');
        $fixedCgRateCode = $this->createRateCode($cgRate * 4, 'caregiver', true);

        $this->attachCaregiver([
            'client_hourly_id' => $hourlyClRateCode->id,
            'caregiver_hourly_id' => $hourlyCgRateCode->id,
            'caregiver_fixed_id' =>  $fixedCgRateCode->id,
            'client_fixed_id' => $fixedClRateCode->id,
        ]);

        $hourlySchedule = $this->makeSchedule([]);
        $fixedSchedule = $this->makeSchedule(['fixed_rates' => true]);

        $hourlyRates = $this->rateFactory->getRatesForSchedule($hourlySchedule);
        $fixedRates = $this->rateFactory->getRatesForSchedule($fixedSchedule);

        $this->assertEquals($clientRate, $hourlyRates->client_rate);
        $this->assertEquals($cgRate, $hourlyRates->caregiver_rate);
        $this->assertEquals($clientRate * 4, $fixedRates->client_rate);
        $this->assertEquals($cgRate * 4, $fixedRates->caregiver_rate);
    }

    public function test_rate_codes_can_be_overridden_on_schedules()
    {
        $this->setSettings(['use_rate_codes' => 1, 'rate_structure' => 'client_rate']);

        $hourlyClRateCode = $this->createRateCode(20, 'client');
        $fixedClRateCode = $this->createRateCode(80, 'client', true);
        $this->client->setDefaultHourlyRate($hourlyClRateCode);
        $this->client->setDefaultFixedRate($fixedClRateCode);

        $hourlyCgRateCode = $this->createRateCode(12, 'caregiver');
        $fixedCgRateCode = $this->createRateCode(60, 'caregiver', true);
        $this->caregiver->setDefaultHourlyRate($hourlyCgRateCode);
        $this->caregiver->setDefaultFixedRate($fixedCgRateCode);

        $scheduleHourlyClientRate = $this->createRateCode($clientRate = 25, 'client');
        $scheduleFixedClientRate = $this->createRateCode($clientRate * 4, 'client');
        $scheduleHourlyCaregiverRate = $this->createRateCode($caregiverRate = 18, 'caregiver');
        $scheduleFixedCaregiverRate = $this->createRateCode($caregiverRate * 4, 'caregiver');

        $hourlySchedule = $this->makeSchedule(['caregiver_rate_id' => $scheduleHourlyCaregiverRate->id, 'client_rate_id' => $scheduleHourlyClientRate->id]);
        $fixedSchedule = $this->makeSchedule(['caregiver_rate_id' => $scheduleFixedCaregiverRate->id, 'client_rate_id' => $scheduleFixedClientRate->id, 'fixed_rates' => true]);

        $hourlyRates = $this->rateFactory->getRatesForSchedule($hourlySchedule);
        $fixedRates = $this->rateFactory->getRatesForSchedule($fixedSchedule);


        $this->assertEquals($clientRate, $hourlyRates->client_rate);
        $this->assertEquals($caregiverRate, $hourlyRates->caregiver_rate);
        $this->assertEquals($clientRate * 4, $fixedRates->client_rate);
        $this->assertEquals($caregiverRate * 4, $fixedRates->caregiver_rate);
    }

    /**
     * @todo This test needs to be updated for the new billing system, if applicable
     */
    public function that_a_different_payment_method_affects_the_ally_fee()
    {
        config()->set('ally.bank_account_fee', 0.03);
        config()->set('ally.credit_card_fee', 0.05);
        $this->setSettings(['use_rate_codes' => 0]);
        $this->attachCaregiver([
            'caregiver_hourly_rate' =>  10,
            'provider_hourly_fee' => 5,
        ]);

        $bankAccount = factory(BankAccount::class)->create();
        $this->client->setPaymentMethod($bankAccount);

        $rates = $this->rateFactory->getRatesForClientCaregiver($this->client, $this->caregiver, false);
        $this->assertEquals(0.45, $rates->ally_fee);
        $this->assertEquals(15.45, $rates->total_rate);
    }

    /**
     * @todo This test needs to be updated for the new billing system, if applicable
     */
    public function that_a_different_payment_method_affects_the_ally_fee_from_scheduling()
    {
        config()->set('ally.bank_account_fee', 0.03);
        config()->set('ally.credit_card_fee', 0.05);
        $this->setSettings(['use_rate_codes' => 0]);

        $schedule = $this->makeSchedule([
            'caregiver_rate' =>  10,
            'provider_fee' => 5,
        ]);

        $bankAccount = factory(BankAccount::class)->create();
        $this->client->setPaymentMethod($bankAccount);

        $rates = $this->rateFactory->getRatesForSchedule($schedule, false);
        $this->assertEquals(0.45, $rates->ally_fee);
        $this->assertEquals(15.45, $rates->total_rate);
    }

    public function test_missing_client_caregiver_pivot_results_in_zero_rates()
    {
        $rates = $this->rateFactory->getRatesForClientCaregiver($this->client, $this->caregiver);

        $this->assertEquals(0, $rates->client_rate);
    }

    protected function makeSchedule(array $data) {
        return factory(Schedule::class)->create(array_merge([
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'business_id' => $this->business->id,
            'starts_at' => Carbon::now(),
            'duration' => 240,
            'caregiver_rate_id' => null,
            'client_rate_id' => null,
            'fixed_rates' => false,
        ], $data));
    }
}
