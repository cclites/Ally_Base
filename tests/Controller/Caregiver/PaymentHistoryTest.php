<?php

namespace Tests\Controller\Caregiver;

use App\Business;
use App\BusinessChain;
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
        $this->disableExceptionHandling();
        $caregiver = factory(Caregiver::class)->create(['firstname' => 'Boov', 'lastname' => 'Clapstein']);
        $chain = factory(BusinessChain::class)->create();
        $business = factory(Business::class)->create(['chain_id' => $chain->id]);
        $chain->assignCaregiver($caregiver);

        $this->actingAs($caregiver->user);

        $response = $this->get('/reports/payment-history/print/'.Carbon::now()->year."/html");

        $response->assertStatus(200);
        $response->assertSeeText('Boov Clapstein');
    }
}
