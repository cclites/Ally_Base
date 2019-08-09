<?php

namespace Tests\Controller\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Business;
use App\OfficeUser;
use App\Caregiver;

class TestReports extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $officeUser;
    protected $business;

    public function setUp()
    {
        parent::setUp();

        $this->business   = factory( Business::class )->create();
        $this->officeUser = factory( OfficeUser::class )->create();

        $this->actingAs( $this->officeUser->user );
        $this->officeUser->businesses()->attach( $this->business->id );
    }

    /**
     * @test
     * 
     * verifying the results of the Caregiver Directory Report
     */
    public function caregiver_directory_report_works_properly()
    {
        $this->withoutExceptionHandling();

        $data = factory( Caregiver::class, 15 )->create()->each( function( $caregiver ){

            $caregiver->businesses()->attach( $this->business );
        });
        // dd( $data->toArray() );

        $query_string = '?json=1';
        // $query_string .= '&start_date=08/02/2019';
        // $query_string .= '&end_date=08/10/2019';
        $query_string .= '&current_page=2';
        $query_string .= '&per_page=5';
        $query_string .= '&active=true';

        $results = $this->get( route( 'business.reports.caregiver_directory' ) . $query_string )
            ->assertSuccessful();

        // dd( $results );
    }
}
