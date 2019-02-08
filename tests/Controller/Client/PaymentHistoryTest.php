<?php

namespace Tests\Controller\Client;

use App\Client;
use App\Billing\Payment;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected $client;

    public function setUp()
    {
        parent::setUp();
        $this->client = factory(Client::class)->create();
        $this->actingAs($this->client->user);
    }

    public function testAClientCanSeeTheirPaymentHistory()
    {
        $response = $this->get('/payment-history');
        $response->assertStatus(200);
        $response->assertSeeText('Payment History');
    }

//    public function testAClientCanDownloadAPaymentDetailsPdf()
//    {
//        $payment = factory(Payment::class)->create(['client_id' => $this->client->id]);
//
//        $response = $this->get("/payment-history/{$payment->id}/print");
//
//        $this->assertTrue($response->headers->get('content-type') == 'application/pdf');
//    }
}
