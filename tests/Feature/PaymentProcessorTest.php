<?php

namespace Tests\Feature;

use App\Business;
use App\Client;
use App\CreditCard;
use App\Payments\PaymentProcessor;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentProcessorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Business
     */
    public $business;

    /**
     * @var Client[]
     */
    public $clients;

    /**
     * @var PaymentProcessor
     */
    public $processor;

    public function setUp()
    {
        parent::setUp();

        $this->business = factory(Business::class)->create();
        $this->clients = factory(Client::class, 3)->create(['business_id' => $this->business->id, 'fee_override' => 0]);
        foreach($this->clients as $client) {
            // Create shift for client
            factory(Shift::class)->create([
                'checked_in_time' => '2018-01-30 12:00:00',
                'checked_out_time' => '2018-01-30 13:00:00',
                'client_id' => $client->id,
                'caregiver_id' => 1,
                'business_id' => $this->business->id,
                'caregiver_rate' => '10.00',
                'provider_fee' => '5.00',
                'status' => Shift::WAITING_FOR_CHARGE,
            ]);

            // Create cc for client
            $client->setPaymentMethod(factory(CreditCard::class)->make());
        }

        $this->processor = new PaymentProcessor($this->business, new Carbon('2018-01-01'), new Carbon('2018-02-01'));
    }

    public function test_a_business_has_a_zero_default_payment()
    {
        $payments = $this->processor->getPaymentModels();

        $this->assertCount(3, $payments);
        foreach($payments as $payment) {
            $this->assertNotNull($payment->client_id);
        }
    }

    public function test_a_business_aggregates_client_payments()
    {
        // Set first two of three clients to provider pay
        $this->clients[0]->setPaymentMethod($this->business);
        $this->clients[1]->setPaymentMethod($this->business);

        $payments = $this->processor->getPaymentModels();
        $this->assertCount(2, $payments);
        $this->assertNull($payments[0]->client_id);
        $this->assertEquals('30', $payments[0]->amount);
    }

    public function test_clients_are_excluded_when_on_hold()
    {
        // Set first client to on hold
        $this->clients[0]->addHold();

        $payments = $this->processor->getPaymentModels();
        $this->assertCount(2, $payments);
    }

    public function test_business_is_excluded_when_on_hold()
    {
        // Set first two of three clients to provider pay
        $this->clients[0]->setPaymentMethod($this->business);
        $this->clients[1]->setPaymentMethod($this->business);

        // Set business to on hold and get a new processor
        $this->business->addHold();

        $payments = $this->processor->getPaymentModels();
        $this->assertCount(1, $payments);
    }
}