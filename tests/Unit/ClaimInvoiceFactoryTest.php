<?php

namespace Tests\Feature;

use App\Exceptions\CannotDeleteClaimInvoiceException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Billing\Generators\ClientInvoiceGenerator;
use App\Billing\Exceptions\PayerAllowanceExceeded;
use App\Billing\Exceptions\InvalidClientPayers;
use Tests\CreatesClientInvoiceResources;
use App\Claims\ClaimInvoiceFactory;
use App\Claims\ClaimInvoiceItem;
use App\Billing\ClientInvoice;
use App\Claims\ClaimInvoice;
use App\Billing\ClaimStatus;
use Tests\CreatesBusinesses;
use App\ClaimableExpense;
use App\ClaimableService;
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
    function it_can_create_a_claim_invoice_from_a_client_invoice()
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

    /** @test */
    function it_can_delete_a_claim_invoice()
    {
        $this->createService(20.00);
        $this->createShiftWithMileage(30.00, 15);
        $invoice = $this->createClientInvoice();
        $claim = $this->claimGenerator->createFromClientInvoice($invoice);
        $this->assertInstanceOf(ClaimInvoice::class, $claim);

        $this->assertCount(3, ClaimInvoiceItem::all());
        $this->assertCount(2, ClaimableService::all());
        $this->assertCount(1, ClaimableExpense::all());

        $this->claimGenerator->deleteClaimInvoice($claim);

        $this->assertNull(ClaimInvoice::find($claim->id));
        $this->assertCount(0, ClaimInvoiceItem::all());
        $this->assertCount(0, ClaimableService::all());
        $this->assertCount(0, ClaimableExpense::all());
    }

    /** @test */
    function it_cannot_delete_a_claim_that_has_already_been_transmitted()
    {
        $this->createService(20.00);
        $invoice = $this->createClientInvoice();
        $claim = $this->claimGenerator->createFromClientInvoice($invoice);
        $claim->update(['status' => ClaimStatus::TRANSMITTED()]);

        $this->expectException(CannotDeleteClaimInvoiceException::class);
        $this->claimGenerator->deleteClaimInvoice($claim);

        $this->assertNotNull(ClaimInvoice::find($claim->id));
    }
}