<?php

namespace Tests\Feature;

use App\Billing\ClientPayer;
use App\Billing\Payer;
use App\Shift;
use Carbon\Carbon;
use Tests\CreatesBusinesses;
use Tests\CreatesShifts;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModifyShiftsTest extends TestCase
{
    use RefreshDatabase, CreatesBusinesses, CreatesShifts;

    /**
     * @var \App\Shift
     */
    private $shift;

    protected function setUp() : void
    {
        parent::setUp();

        $this->createBusinessWithUsers();
        $this->service = $this->createDefaultService($this->chain);
    }

    /** @test */
    function an_hourly_shift_can_update_the_a_payer()
    {
        $this->actingAs($this->officeUser->user);

        $this->shift = $this->createShift(Carbon::today(), '08:00', 4, ['payer_id' => null, 'status' => Shift::WAITING_FOR_CONFIRMATION]);
        $this->assertNull($this->shift->payer);

        $clientPayer = $this->createEffectivePayer();
        $data = array_merge($this->shift->toArray(), ['payer_id' => $clientPayer->payer->id]);

        $this->patchJson(route('business.shifts.update', ['shift' => $this->shift]), $data)
            ->assertStatus(200);

        $this->assertEquals($clientPayer->payer->id, $this->shift->fresh()->payer->id);
    }
}
