<?php

namespace Tests\Feature;

use App\Billing\ClientInvoice;
use App\Billing\Exceptions\InvalidClientPayers;
use App\Billing\Exceptions\PayerAllowanceExceeded;
use App\Billing\Generators\ClientInvoiceGenerator;
use App\Claims\ClaimInvoice;
use App\Claims\ClaimInvoiceFactory;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesBusinesses;
use Tests\CreatesClientInvoiceResources;
use Tests\TestCase;

class ClaimInvoiceFactoryTest extends TestCase
{
    use RefreshDatabase;
    use CreatesClientInvoiceResources;
    use CreatesBusinesses;

    /** @var ClientInvoiceGenerator */
    private $invoicer;

    /** @var \App\Billing\ClientPayer */
    private $clientPayer;

    /** @var ClaimInvoiceFactory */
    private $claimGenerator;

    protected function setUp()
    {
        parent::setUp();

        $this->createBusinessWithUsers(true);
        $this->clientPayer = $this->createBalancePayer();
        $this->invoicer = app(ClientInvoiceGenerator::class);
        $this->claimGenerator = app(ClaimInvoiceFactory::class);
    }

    /**
     * Helper to create a client invoice.
     *
     * @return ClientInvoice
     */
    public function createClientInvoice() : ClientInvoice
    {
        try {
            $invoice = $this->invoicer->generateAll($this->client)[0];
        } catch (PayerAllowanceExceeded $ex) {
            $invoice = null;
        } catch (InvalidClientPayers $ex) {
            $invoice = null;
        }

        $this->assertInstanceOf(ClientInvoice::class, $invoice);
        return $invoice;
    }

    /**
     * @test
     * @throws \Exception
     */
    function a_claim_invoice_can_be_created_from_a_client_invoice()
    {
        $this->createService(20.00);
        $this->createShiftWithMileage(30.00, 15);
        $invoice = $this->createClientInvoice();

        // Expects 3 items a shift_service, a shift and a shift_expense
        $this->assertCount(3, $invoice->items);

        $claim = $this->claimGenerator->createFromClientInvoice($invoice);

        $this->assertInstanceOf(ClaimInvoice::class, $claim);

        $this->assertCount(3, $claim->items);
    }
}
