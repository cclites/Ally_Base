<?php
namespace Tests\Feature;

use App\Billing\Validators\ClientPayerValidator;
use App\Client;
use App\Billing\ClientPayer;
use App\Billing\Payer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientPayerTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Client */
    protected $client;

    /** @var ClientPayerValidator */
    protected $validator;

    protected function setUp() : void
    {
        parent::setUp();
        $this->client = factory(Client::class)->create();
        $this->validator = new ClientPayerValidator();
    }

    protected function createPayer(string $allocationType, array $clientPayerOptions = [], array $payerOptions = []): ClientPayer {
        $payer = factory(Payer::class)->create($payerOptions);
        return factory(ClientPayer::class)->create([
            'payer_id' => $payer->id,
            'client_id' => $this->client->id,
            'payment_allocation' => $allocationType
        ] + $clientPayerOptions + ['effective_start' => '2019-01-01']);
    }

    protected function validate($client = null)
    {
        $client = $client ?? $this->client;
        return $this->validator->validate($client);
    }

    /**
     * @test
     */
    function a_payer_can_be_added_to_a_client()
    {
        $clientPayer = $this->createPayer('balance');
        $this->assertEquals($this->client->id, $clientPayer->client_id);
    }

    /**
     * @test
     */
    function a_single_balance_payer_is_valid()
    {
        $this->createPayer('balance');

        $this->assertTrue($this->validate(), $this->validator->getErrorMessage());
    }

    /**
     * @test
     */
    function an_allowance_type_without_a_balance_payer_is_invalid()
    {
        $this->createPayer('weekly');

        $this->assertFalse($this->validate());
    }

    /**
     * @test
     */
    function a_split_type_that_adds_up_to_1_is_valid()
    {
        $this->createPayer('split', ['split_percentage' => 0.49]);
        $this->createPayer('split', ['split_percentage' => 0.51]);

        $this->assertTrue($this->validate(), $this->validator->getErrorMessage());
    }

    /**
     * @test
     */
    function a_split_type_that_adds_up_to_less_than_1_is_invalid()
    {
        $this->createPayer('split', ['split_percentage' => 0.49]);
        $this->createPayer('split', ['split_percentage' => 0.50]);

        $this->assertFalse($this->validate());
    }

    /**
     * @test
     */
    function a_split_type_less_than_1_that_has_a_balance_payer_is_valid()
    {
        $this->createPayer('split', ['split_percentage' => 0.49]);
        $this->createPayer('balance');

        $this->assertTrue($this->validate(), $this->validator->getErrorMessage());
    }


    /**
     * @test
     */
    function two_balance_payers_that_overlap_is_invalid()
    {
        $this->createPayer('balance', ['effective_start' => '2019-01-01', 'effective_end' => '2021-12-31']);
        $this->createPayer('balance', ['effective_start' => '2020-01-01']);

        $this->assertFalse($this->validate());
    }

    /**
     * @test
     */
    function two_balance_payers_that_do_not_overlap_is_valid()
    {
        $this->createPayer('balance', ['effective_start' => '2019-01-01', 'effective_end' => '2021-12-31']);
        $this->createPayer('balance', ['effective_start' => '2022-01-01']);

        $this->assertTrue($this->validate(), $this->validator->getErrorMessage());
    }

    /**
     * @test
     */
    function a_split_payer_in_the_future_that_adds_up_to_more_than_1_is_invalid()
    {
        $this->createPayer('split', ['split_percentage' => 0.49]);
        $this->createPayer('split', ['split_percentage' => 0.51, 'effective_end' => '2019-12-31']);
        $this->createPayer('split', ['split_percentage' => 0.52, 'effective_start' => '2020-01-01']);

        $this->assertFalse($this->validate());
    }

    /**
     * @test
     */
    function a_split_payer_in_the_future_that_equals_1_is_valid()
    {
        $this->createPayer('split', ['split_percentage' => 0.49, 'effective_end' => '2019-12-31']);
        $this->createPayer('split', ['split_percentage' => 0.51, 'effective_end' => '2019-12-31']);
        $this->createPayer('split', ['split_percentage' => 0.52, 'effective_start' => '2020-01-01']);
        $this->createPayer('split', ['split_percentage' => 0.48, 'effective_start' => '2020-01-01']);

        $this->assertTrue($this->validate(), $this->validator->getErrorMessage());
    }

    /**
     * @test
     */
    function a_gap_in_dates_is_invalid()
    {
        $this->createPayer('balance', ['effective_start' => '2019-01-01', 'effective_end' => '2021-12-31']);
        $this->createPayer('balance', ['effective_start' => '2022-01-04']);

        $this->assertFalse($this->validate());
    }

    /**
     * @test
     */
    function a_gap_from_now_is_invalid()
    {
        // Dates start from 2 days in the future..
        $start = Carbon::now()->addDays(2);
        $end = $start->copy()->addYear();

        $this->createPayer('balance', ['effective_start' => $start->toDateString(), 'effective_end' => $end->toDateString()]);
        $this->createPayer('balance', ['effective_start' => $end->addDay()->toDateString()]);

        $this->assertFalse($this->validate());
    }

    /**
     * @test
     */
    function a_gap_at_the_end_is_invalid()
    {
        // Dates only go 1 year in the future
        $end = Carbon::now()->addYear();

        $this->createPayer('balance', ['effective_start' => '2019-01-01', 'effective_end' => $end->toDateString()]);

        $this->assertFalse($this->validate());
    }

    /**
     * @test
     */
    function an_allowance_payer_returns_valid_allowance_ranges_for_dates()
    {
        $weeklyPayer = $this->createPayer('weekly', ['effective_start' => '2019-01-01'], ['week_start' => 1]);
        $monthlyPayer = $this->createPayer('monthly', ['effective_start' => '2019-01-01']);
        $dailyPayer = $this->createPayer('daily', ['effective_start' => '2019-01-01']);
        $balancePayer = $this->createPayer('balance', ['effective_start' => '2019-01-01']);

        $this->assertEquals('2019-02-11 00:00:00', $weeklyPayer->getAllowanceRange('2019-02-12')->start()->toDateTimeString());
        $this->assertEquals('2019-02-17 23:59:59', $weeklyPayer->getAllowanceRange('2019-02-12')->end()->toDateTimeString());
        $this->assertEquals('2019-02-01 00:00:00', $monthlyPayer->getAllowanceRange('2019-02-12')->start()->toDateTimeString());
        $this->assertEquals('2019-02-28 23:59:59', $monthlyPayer->getAllowanceRange('2019-02-12')->end()->toDateTimeString());
        $this->assertEquals('2019-02-12 00:00:00', $dailyPayer->getAllowanceRange('2019-02-12')->start()->toDateTimeString());
        $this->assertEquals('2019-02-12 23:59:59', $dailyPayer->getAllowanceRange('2019-02-12')->end()->toDateTimeString());
        $this->assertNull($balancePayer->getAllowanceRange('2019-02-12'));
    }

    /** @test */
    function weekly_allowance_payers_should_use_the_weekly_start_value_for_date_ranges()
    {
        $weeklyPayer = $this->createPayer('weekly', ['effective_start' => '2019-01-01'], ['week_start' => 2]);
        $this->assertEquals('2019-02-12 00:00:00', $weeklyPayer->getAllowanceRange('2019-02-13')->start()->toDateTimeString());
        $this->assertEquals('2019-02-18 23:59:59', $weeklyPayer->getAllowanceRange('2019-02-13')->end()->toDateTimeString());
    }

    /**
     * @test
     */
    function two_of_the_same_payer_that_overlap_is_invalid()
    {
        $clientPayerA = $this->createPayer('balance', ['effective_start' => '2019-01-01', 'effective_end' => '2021-12-31']);
        $this->createPayer('manual', ['payer_id' => $clientPayerA->payer_id, 'effective_start' => '2020-01-01']);

        $this->assertFalse($this->validate());
    }

    /**
     * @test
     */
    public function mixing_an_offline_payer_with_an_online_payer_should_fail()
    {
        $clientPayerA = $this->createPayer('split', ['split_percentage' => 0.5, 'effective_start' => '2019-01-01']);
        $clientPayerB = factory(ClientPayer::class)->create([
            'payer_id' => Payer::OFFLINE_PAY_ID,
            'client_id' => $this->client->id,
            'payment_allocation' => 'split',
            'split_percentage' => 0.5,
            'effective_start' => '2019-01-01',
        ]);

        $this->assertFalse($this->validate());
        $this->assertStringContainsString('offline', $this->validator->getErrorMessage());
    }
}