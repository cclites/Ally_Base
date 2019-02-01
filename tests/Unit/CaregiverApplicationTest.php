<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\BusinessChain;
use App\CaregiverApplication;
use App\Caregiver;
use Carbon\Carbon;

class CaregiverApplicationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_converted_into_a_caregiver()
    {
        $chain = factory(BusinessChain::class)->create();

        $application = factory(CaregiverApplication::class)->create([
            'chain_id' => $chain,
            'status' => CaregiverApplication::STATUS_NEW,
        ]);
        $this->assertEquals(CaregiverApplication::STATUS_NEW, $application->fresh()->status);

        $caregiver = $application->convertToCaregiver();

        $this->assertInstanceOf(Caregiver::class, $caregiver);
        $this->assertCount(1, $chain->fresh()->caregivers);
        $this->assertEquals(CaregiverApplication::STATUS_CONVERTED, $application->fresh()->status);
    }

    /** @test */
    public function it_should_set_the_caregivers_application_date_when_converted()
    {
        $chain = factory(BusinessChain::class)->create();

        $application = factory(CaregiverApplication::class)->create([
            'chain_id' => $chain,
        ]);

        $caregiver = $application->convertToCaregiver();

        $this->assertEquals(
            Carbon::now()->toDateString(),
            optional($caregiver->application_date)->toDateString()
        );
    }
}
