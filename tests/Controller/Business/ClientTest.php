<?php

namespace Tests\Controller\Business;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Billing\ClientInvoice;
use Tests\CreatesBusinesses;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase, WithFaker, CreatesBusinesses;

    public function setUp() : void
    {
        parent::setUp();

        $this->createBusinessWithUsers();
        $this->actingAs($this->officeUser->user);
    }

    /** @test */
    public function an_office_user_can_update_client_hospital_info()
    {
        $this->assertEquals(1, $this->officeUser->businesses()->first()->clients()->count());

        $name = "Test Hospital";
        $number = "1234567890";

        $this->client->update([
            'hospital_name' => $name,
            'hospital_number' => $number,
        ]);

        $this->client = $this->client->fresh();
        $this->assertEquals($name, $this->client->hospital_name);
        $this->assertEquals($number, $this->client->hospital_number);
    }

    /** @test */
    public function an_office_user_can_deactivate_a_client()
    {
        $this->assertEquals(1, $this->client->active);

        $this->postJson(route('business.clients.deactivate', ['client' => $this->client]), ['active' => false])
            ->assertStatus(200);

        $this->assertEquals(0, $this->client->fresh()->active, "Client was not deactivated");
    }

    /** @test */
    public function a_client_cannot_be_deactivated_if_they_have_unpaid_invoices()
    {
        $this->assertEquals(1, $this->client->active);

        // Create an unpaid invoice
        $invoice = factory(ClientInvoice::class)->create(['client_id' => $this->client->id, 'amount' => 100, 'amount_paid' => 0, 'offline' => 0]);

        $this->assertEquals(1, $this->client->getUnpaidInvoicesCount());

        $this->postJson(route('business.clients.deactivate', ['client' => $this->client]), ['active' => false])
            ->assertStatus(400);

        $this->assertEquals(1, $this->client->fresh()->active);
    }

}