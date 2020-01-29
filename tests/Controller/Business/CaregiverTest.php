<?php

namespace Tests\Feature\Controller\Business;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Billing\CaregiverInvoice;
use Tests\CreatesBusinesses;
use Tests\TestCase;

class CaregiverTest extends TestCase
{
    use RefreshDatabase, WithFaker, CreatesBusinesses;

    public function setUp() : void
    {
        parent::setUp();

        $this->createBusinessWithUsers();
        $this->actingAs($this->officeUser->user);
    }

    /** @test */
    function an_office_user_can_deactivate_a_caregiver()
    {
        $this->assertEquals(1, $this->caregiver->active);

        $this->deleteJson(route('business.caregivers.destroy', ['caregiver' => $this->caregiver]))
            ->assertStatus(200);

        $this->assertEquals(0, $this->caregiver->fresh()->active, "Caregiver was not deactivated");
    }

    /** @test */
    function a_caregiver_cannot_be_deactivated_if_they_have_unpaid_invoices()
    {
        $this->assertEquals(1, $this->caregiver->active);

        // Create an unpaid invoice
        factory(CaregiverInvoice::class)->create(['caregiver_id' => $this->caregiver->id, 'amount' => 100, 'amount_paid' => 0]);

        $this->deleteJson(route('business.caregivers.destroy', ['caregiver' => $this->caregiver]))
            ->assertStatus(400);

        $this->assertEquals(1, $this->caregiver->fresh()->active);
    }
}
