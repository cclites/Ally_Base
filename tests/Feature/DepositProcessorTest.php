<?php

namespace Tests\Feature;

use App\Business;
use App\Client;
use App\Caregiver;
use App\Payments\DepositProcessor;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepositProcessorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Business
     */
    public $business;

    /**
     * @var Client
     */
    public $client;

    /**
     * @var Caregiver[]
     */
    public $caregivers;

    /**
     * @var Shift[]
     */
    public $shifts;

    /**
     * @var \App\Payments\DepositProcessor
     */
    public $processor;

    public function setUp()
    {
        parent::setUp();

        $this->business = factory(Business::class)->create();
        $this->client = factory(Client::class)->create(['business_id' => $this->business->id]);
        $this->caregivers = factory(Caregiver::class, 3)->create();
        foreach($this->caregivers as $caregiver) {
            // Attach to business
            $this->business->caregivers()->attach($caregiver->id);

            // Attach to client
            $this->client->caregivers()->attach($caregiver->id);

            // Create shift for client
            $this->shifts[] = factory(Shift::class)->create([
                'checked_in_time' => '2018-01-30 12:00:00',
                'checked_out_time' => '2018-01-30 13:00:00',
                'client_id' => $this->client->id,
                'caregiver_id' => $caregiver->id,
                'business_id' => $this->business->id,
                'caregiver_rate' => '10.00',
                'provider_fee' => '5.00',
                'status' => Shift::WAITING_FOR_PAYOUT,
            ]);
        }

        $this->processor = new DepositProcessor($this->business, new Carbon('2018-01-01'), new Carbon('2018-02-01'));
    }

    public function test_deposit_amounts()
    {
        $deposits = $this->processor->getDepositData();
        $this->assertCount(4, $deposits);
        foreach($deposits as $deposit) {
            if ($deposit->business_id) {
                $this->assertEquals('15.00', $deposit->amount);
            }
            else {
                $this->assertEquals('10.00', $deposit->amount);
            }
        }
    }

    public function test_deposits_arent_processed_for_paid_status()
    {
        foreach($this->shifts as $shift) {
            $shift->update(['status' => 'PAID']);
        }

        $deposits = $this->processor->getDepositData();
        $this->assertCount(0, $deposits);
    }

    public function test_deposits_arent_processed_for_business_when_paid_business_only()
    {
        foreach($this->shifts as $shift) {
            $shift->update(['status' => 'PAID_BUSINESS_ONLY']);
        }

        $deposits = $this->processor->getDepositData();
        $this->assertCount(3, $deposits);
        foreach($deposits as $deposit) {
            $this->assertNull($deposit->business_id);
        }
    }

    public function test_deposits_arent_processed_for_caregiver_when_paid_caregiver_only()
    {
        foreach($this->shifts as $shift) {
            $shift->update(['status' => 'PAID_CAREGIVER_ONLY']);
        }

        $deposits = $this->processor->getDepositData();
        $this->assertCount(1, $deposits);
        foreach($deposits as $deposit) {
            $this->assertNotNull($deposit->business_id);
        }
    }

    public function test_caregivers_are_excluded_when_on_hold()
    {
        // Set first client to on hold
        $this->caregivers[0]->addHold();

        $deposits = $this->processor->getDepositData();
        $this->assertCount(3, $deposits);
    }

    public function test_business_is_excluded_when_on_hold()
    {
        // Set business to on hold and get a new processor
        $this->business->addHold();

        $deposits = $this->processor->getDepositData();
        $this->assertCount(3, $deposits);
        foreach($deposits as $deposit) {
            $this->assertNull($deposit->business_id);
        }
    }
}