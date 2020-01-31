<?php

namespace Tests\Feature;

use App\Billing\Actions\ApplyPayment;
use App\Billing\Generators\ClientInvoiceGenerator;
use App\Billing\Payment;
use App\Client;
use Tests\CreatesClientInvoiceResources;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApplyPaymentTest extends TestCase
{
    use RefreshDatabase;
    use CreatesClientInvoiceResources;

    /** @var ApplyPayment */
    private $paymentApplicator;

    function setUp() : void
    {
        parent::setUp();

        $this->client = factory(Client::class)->create();
        $this->createBalancePayer();

        $this->paymentApplicator = app(ApplyPayment::class);
    }

    /**
     * @test
     */
    public function an_invoiceable_should_receive_only_its_allocated_ally_fee_on_a_full_payment()
    {
        $serviceA = $this->createService(100.00);
        $serviceB = $this->createService(50.00);
        $serviceC = $this->createServiceHours(15.00, 3); // 45.00 total

        $invoices = app(ClientInvoiceGenerator::class)->generateAll($this->client);
        $payment = factory(Payment::class)->create([
            'amount' => 195,
            'system_allotment' => 9.75,
        ]);

        $this->paymentApplicator->apply($invoices[0], $payment);

        $this->assertEquals(5.00, $serviceA->getAllyRate());
        $this->assertEquals(2.50, $serviceB->getAllyRate());
        $this->assertEquals(0.75, $serviceC->getAllyRate());
    }

    /**
     * @test
     */
    public function an_invoiceable_should_receive_only_its_allocated_ally_fee_of_a_partial_payment()
    {
        $serviceA = $this->createService(100.00);
        $serviceB = $this->createService(50.00);
        $serviceC = $this->createServiceHours(15.00, 3); // 45.00 total

        $invoices = app(ClientInvoiceGenerator::class)->generateAll($this->client);
        $payment = factory(Payment::class)->create([
            'amount' => 150,
            'system_allotment' => 7.5,
        ]);

        $this->paymentApplicator->apply($invoices[0], $payment, 100);

        $this->assertEquals(2.56, $serviceA->getAllyRate());
        $this->assertEquals(1.28, $serviceB->getAllyRate());
        $this->assertEquals(0.3833, $serviceC->getAllyRate());
    }
}
