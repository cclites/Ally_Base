<?php

namespace Tests\Feature;

use App\Billing\Service;
use App\OfficeUser;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesBusinesses;
use Tests\CreatesSchedules;

class OpenShiftControllerTest extends TestCase
{
    use RefreshDatabase, CreatesBusinesses, CreatesSchedules;

    /**
     * array of sample times to cycle through
     */
    private $times = [

        'next monday',
        'yesterday',
        'today',
        'today +7 days',
        'today +8 days'
    ];

    protected function getRandomTime( $iterator )
    {

        return Carbon::parse( $this->times[ $iterator % count( $this->times ) ] );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->createBusinessWithUsers();

        $this->service = factory( Service::class )->create(['name' => 'General', 'chain_id' => $this->chain->id, 'default' => true]);

        for( $i = 0; $i < 50; $i++ ){

            $this->createSchedule( $this->getRandomTime( $i ), ( $i % 12 + 1 ) . ':00:00', 1 ); // creates one for every hour lasting an hour each
        }
    }

    /**
     * @test
     */
    public function office_users_can_view_open_shifts()
    {
        $this->actingAs( $this->officeUser->user );

        $data = $this->get( route( 'business.open-shifts.index' ) . '?json=1' );
    }
}
