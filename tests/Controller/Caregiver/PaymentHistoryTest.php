<?php

namespace Tests\Controller\Caregiver;

use App\Business;
use App\Caregiver;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentHistoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testACaregiverCanPrintTheirPaymentHistory()
    {
        $caregiver = factory(Caregiver::class)->create();
        $business = factory(Business::class)->create();
        $business->caregivers()->attach($caregiver->id);

        $this->actingAs($caregiver->user);

        $response = $this->get('/reports/payment-history/print/'.Carbon::now()->year);

        $response->assertStatus(200);
        $response->assertSeeText(htmlentities($caregiver->firstname));
        $response->assertSeeText(htmlentities($caregiver->lastname));
    }
}
