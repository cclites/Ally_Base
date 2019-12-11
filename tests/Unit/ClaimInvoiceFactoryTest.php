<?php

namespace Tests\Feature;

use App\Claims\ClaimAdjustment;
use App\Claims\ClaimAdjustmentType;
use App\Claims\Exceptions\CannotDeleteClaimInvoiceException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Billing\Generators\ClientInvoiceGenerator;
use App\Billing\Exceptions\PayerAllowanceExceeded;
use App\Billing\Exceptions\InvalidClientPayers;
use App\Claims\Factories\ClaimInvoiceFactory;
use Tests\CreatesClientInvoiceResources;
use App\Claims\ClaimableExpense;
use App\Claims\ClaimableService;
use App\Claims\ClaimInvoiceType;
use App\Claims\ClaimInvoiceItem;
use App\Billing\ClientInvoice;
use App\Claims\ClaimInvoice;
use App\Billing\ClaimStatus;
use Tests\CreatesBusinesses;
use App\Billing\Payer;
use App\Business;
use Tests\TestCase;
use App\Client;

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
        $this->payer = factory(Payer::class)->create(['name' => 'Test Insurance Company']);
        $this->clientPayer = $this->createBalancePayer('2019-01-01', '2099-01-01', $this->payer->id);
        $this->invoicer = app(ClientInvoiceGenerator::class);
        $this->claimGenerator = app(ClaimInvoiceFactory::class);
    }

    /**
     * Helper to create a client invoice.
     *
     * @param Client|null $client
     * @return ClientInvoice
     */
    public function createClientInvoice(Client $client = null) : ClientInvoice
    {
        if (empty($client)) {
            $client = $this->client;
        }
        try {
            $invoice = $this->invoicer->generateAll($client)[0];
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
    public function it_can_create_a_single_claim_invoice_from_a_single_client_invoice()
    {
        $this->createService(20.00);
        $this->createShiftWithMileage(30.00, 15);
        $invoice = $this->createClientInvoice();

        // Expects 3 items a shift_service, a shift and a shift_expense
        $this->assertCount(3, $invoice->items);

        list($claim, $errors) = $this->claimGenerator->createFromClientInvoice($invoice);

        $this->assertInstanceOf(ClaimInvoice::class, $claim);
        $this->assertEquals(ClaimInvoiceType::SINGLE(), $claim->getType());

        $this->assertCount(3, $claim->items);
    }

    /** @test */
    public function it_can_create_a_client_claim_from_multiple_invoices()
    {
        $this->createService(20.00);
        $this->createShiftWithMileage(30.00, 15);
        $invoice = $this->createClientInvoice();

        $this->createService(20.00);
        $this->createShiftWithMileage(30.00, 15);
        $otherInvoice = $this->createClientInvoice();

        $invoices = ClientInvoice::all();
        $this->assertCount(2, $invoices);

        list($claim, $errors) = $this->claimGenerator->createFromClientInvoices($invoices);

        $this->assertInstanceOf(ClaimInvoice::class, $claim);
        $this->assertEquals(ClaimInvoiceType::CLIENT(), $claim->getType());
        $this->assertNotNull($claim->client_id);

        $this->assertCount(6, $claim->items);
    }

    /** @test */
    public function it_can_create_a_payer_claim_from_multiple_invoices()
    {
        $this->createService(20.00);
        $invoice = $this->createClientInvoice();

        $otherClient = factory('App\Client')->create();
        $otherPayer = $this->createBalancePayer('2019-01-01', '9999-12-31', $this->payer->id, $otherClient);
        $this->createService(20.00, '2019-01-15', null, $otherClient);
        $otherInvoice = $this->createClientInvoice($otherClient);

        $this->assertEquals($otherClient->id, $otherInvoice->client_id);
        $this->assertNotEquals($invoice->client_id, $otherInvoice->client_id);

        $invoices = ClientInvoice::all();
        $this->assertCount(2, $invoices);

        list($claim, $errors) = $this->claimGenerator->createFromClientInvoices($invoices);

        $this->assertInstanceOf(ClaimInvoice::class, $claim);
        $this->assertEquals(ClaimInvoiceType::PAYER(), $claim->getType());
        $this->assertNull($claim->client_id);
        $this->assertEquals($claim->payer_name, 'Test Insurance Company');

        $this->assertCount(2, $claim->items);
    }

    /** @test */
    public function it_can_only_create_one_claim_per_client_invoice()
    {
        $this->createService(20.00);
        $invoice = $this->createClientInvoice();
        list($claim, $errors) = $this->claimGenerator->createFromClientInvoice($invoice);
        $this->assertInstanceOf(ClaimInvoice::class, $claim);

        $this->expectException(\InvalidArgumentException::class);
        list($claim, $errors) = $this->claimGenerator->createFromClientInvoice($invoice);
        $this->assertNull($claim);
    }

    /** @test */
    public function it_cannot_create_a_claim_from_invoices_with_different_payers()
    {
        $this->createService(20.00);
        $invoice = $this->createClientInvoice();

        $otherClient = factory('App\Client')->create();
        $otherPayer = $this->createBalancePayer('2019-01-01', '9999-12-31', null, $otherClient);
        $this->createService(20.00, '2019-01-15', null, $otherClient);
        $otherInvoice = $this->createClientInvoice($otherClient);

        $this->assertNotEquals($invoice->clientPayer->payer_id, $otherInvoice->clientPayer->payer_id);

        $this->assertCount(2, ClientInvoice::all());

        $this->expectException(\InvalidArgumentException::class);
        list($claim, $errors) = $this->claimGenerator->createFromClientInvoices(ClientInvoice::all());
        $this->assertNull($claim);
    }

    /** @test */
    public function it_cannot_create_a_claim_from_private_pay_invoices_with_different_clients()
    {
        $this->createService(20.00);
        $this->clientPayer->delete();
        $this->clientPayer = $this->createBalancePayer('2019-01-01', '2099-01-01', Payer::PRIVATE_PAY_ID);
        $invoice = $this->createClientInvoice();

        $otherClient = factory('App\Client')->create();
        $otherPayer = $this->createBalancePayer('2019-01-01', '9999-12-31', Payer::PRIVATE_PAY_ID, $otherClient);
        $this->createService(20.00, '2019-01-15', null, $otherClient);
        $otherInvoice = $this->createClientInvoice($otherClient);

        $this->assertEquals(Payer::PRIVATE_PAY_ID, $invoice->clientPayer->payer_id);
        $this->assertEquals(Payer::PRIVATE_PAY_ID, $otherInvoice->clientPayer->payer_id);

        $this->expectException(\InvalidArgumentException::class);
        list($claim, $errors) = $this->claimGenerator->createFromClientInvoices(ClientInvoice::all());
        $this->assertNull($claim);
    }

    /** @test */
    public function it_cannot_create_a_claim_from_offline_invoices_with_different_clients()
    {
        $this->createService(20.00);
        $this->clientPayer->delete();
        $this->clientPayer = $this->createBalancePayer('2019-01-01', '2099-01-01', Payer::OFFLINE_PAY_ID);
        $invoice = $this->createClientInvoice();

        $otherClient = factory('App\Client')->create();
        $otherPayer = $this->createBalancePayer('2019-01-01', '9999-12-31', Payer::OFFLINE_PAY_ID, $otherClient);
        $this->createService(20.00, '2019-01-15', null, $otherClient);
        $otherInvoice = $this->createClientInvoice($otherClient);

        $this->assertEquals(Payer::OFFLINE_PAY_ID, $invoice->clientPayer->payer_id);
        $this->assertEquals(Payer::OFFLINE_PAY_ID, $otherInvoice->clientPayer->payer_id);

        $this->expectException(\InvalidArgumentException::class);
        list($claim, $errors) = $this->claimGenerator->createFromClientInvoices(ClientInvoice::all());
        $this->assertNull($claim);
    }

    /** @test */
    public function it_cannot_create_a_claim_from_invoices_with_different_business_ids()
    {
        $this->createService(20.00);
        $invoice = $this->createClientInvoice();

        $otherBusiness = factory(Business::class)->create();
        $otherClient = factory('App\Client')->create(['business_id' => $otherBusiness->id]);
        $otherPayer = $this->createBalancePayer('2019-01-01', '9999-12-31', $this->payer->id, $otherClient);
        $this->createService(20.00, '2019-01-15', null, $otherClient);
        $otherInvoice = $this->createClientInvoice($otherClient);

        $this->assertEquals($invoice->clientPayer->payer_id, $otherInvoice->clientPayer->payer_id);
        $this->assertNotEquals($invoice->client->business_id, $otherInvoice->client->business_id);

        $this->expectException(\InvalidArgumentException::class);
        list($claim, $errors) = $this->claimGenerator->createFromClientInvoices(ClientInvoice::all());
        $this->assertNull($claim);
    }

    /** @test */
    public function creating_a_claim_from_multiple_invoices_should_attach_the_invoice_id_to_each_item()
    {
        $this->createService(20.00);
        $this->createShiftWithMileage(30.00, 15);
        $invoice = $this->createClientInvoice();

        $this->createService(20.00);
        $this->createShiftWithMileage(30.00, 15);
        $otherInvoice = $this->createClientInvoice();

        $this->assertCount(3, $invoice->items);
        $this->assertCount(3, $otherInvoice->items);

        list($claim, $errors) = $this->claimGenerator->createFromClientInvoices(ClientInvoice::all());

        $this->assertCount(3, $claim->items->where('client_invoice_id', $invoice->id));
        $this->assertCount(3, $claim->items->where('client_invoice_id', $otherInvoice->id));
    }

    /** @test */
    public function it_can_delete_a_claim_invoice()
    {
        $this->createService(20.00);
        $this->createShiftWithMileage(30.00, 15);
        $invoice = $this->createClientInvoice();
        list($claim, $errors) = $this->claimGenerator->createFromClientInvoice($invoice);
        $this->assertInstanceOf(ClaimInvoice::class, $claim);

        $this->assertCount(3, ClaimInvoiceItem::all());
        $this->assertCount(2, ClaimableService::all());
        $this->assertCount(1, ClaimableExpense::all());

        $this->claimGenerator->deleteClaimInvoice($claim);

        $this->assertNull(ClaimInvoice::find($claim->id));
        $this->assertCount(0, ClaimInvoiceItem::all());
        $this->assertCount(0, ClaimableService::all());
        $this->assertCount(0, ClaimableExpense::all());
        $this->assertCount(0, \DB::table('claim_invoice_client_invoice')->get());
    }

    /** @test */
    public function it_cannot_delete_a_claim_that_has_already_been_transmitted()
    {
        $this->createService(20.00);
        $invoice = $this->createClientInvoice();
        list($claim, $errors) = $this->claimGenerator->createFromClientInvoice($invoice);
        $claim->update(['status' => ClaimStatus::TRANSMITTED()]);

        $this->expectException(CannotDeleteClaimInvoiceException::class);
        $this->claimGenerator->deleteClaimInvoice($claim);

        $this->assertNotNull(ClaimInvoice::find($claim->id));
    }

    /** @test */
    function it_cannot_delete_a_claim_that_has_had_adjustments_made()
    {
        $this->createService(20.00);
        $invoice = $this->createClientInvoice();
        list($claim, $errors) = $this->claimGenerator->createFromClientInvoice($invoice);

        ClaimAdjustment::create([
            'claim_invoice_id' => $claim->id,
            'claim_invoice_item_id' => $claim->items->first()->id,
            'adjustment_type' => ClaimAdjustmentType::PAYMENT(),
            'amount_applied' => $claim->items->first()->amount,
        ]);

        $this->expectException(CannotDeleteClaimInvoiceException::class);

        $this->claimGenerator->deleteClaimInvoice($claim);

        $this->assertNotNull(ClaimInvoice::find($claim->id));
    }

    /** @test */
    function if_cannot_delete_a_claim_item_that_has_had_adjuistments_made()
    {
        $this->createService(20.00);
        $invoice = $this->createClientInvoice();
        list($claim, $errors) = $this->claimGenerator->createFromClientInvoice($invoice);

        ClaimAdjustment::create([
            'claim_invoice_id' => $claim->id,
            'claim_invoice_item_id' => $claim->items->first()->id,
            'adjustment_type' => ClaimAdjustmentType::PAYMENT(),
            'amount_applied' => $claim->items->first()->amount,
        ]);

        $this->expectException(CannotDeleteClaimInvoiceException::class);

        $this->claimGenerator->deleteClaimInvoiceItem($claim->items->first());
        $this->assertCount(1, $claim->fresh()->items);
    }

    /** @test */
    public function it_should_remove_all_related_claimable_data_when_deleting_a_claim_invoice_item()
    {
        $this->createService(20.00);
        $this->createShiftWithMileage(30.00, 15);
        $invoice = $this->createClientInvoice();
        list($claim, $errors) = $this->claimGenerator->createFromClientInvoice($invoice);

        $this->assertCount(2, ClaimableService::all());
        $this->assertCount(1, ClaimableExpense::all());

        $item = ClaimInvoiceItem::where('claimable_type', ClaimableExpense::class)->first();
        $this->claimGenerator->deleteClaimInvoiceItem($item);
        $this->assertCount(0, ClaimableExpense::all());
        $this->assertCount(2, ClaimInvoiceItem::all());

        $item = ClaimInvoiceItem::where('claimable_type', ClaimableService::class)->first();
        $this->claimGenerator->deleteClaimInvoiceItem($item);
        $this->assertCount(1, ClaimableService::all());
        $this->assertCount(1, ClaimInvoiceItem::all());
    }

    /** @test */
    public function it_should_update_the_claim_balance_when_deleting_a_claim_invoice_item_()
    {
        $this->createService(20.00);
        $this->createService(20.00);
        $invoice = $this->createClientInvoice();
        list($claim, $errors) = $this->claimGenerator->createFromClientInvoice($invoice);

        $this->assertEquals(40.00, $claim->amount);
        $this->assertNull($claim->modified_at);

        $this->claimGenerator->deleteClaimInvoiceItem($claim->items->first());

        $this->assertEquals(20.00, $claim->fresh()->amount);
        $this->assertNotNull($claim->fresh()->modified_at);
    }
}
