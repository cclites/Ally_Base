<?php

namespace Tests\Feature;

use App\Business;
use App\Caregiver;
use App\CaregiverLicense;
use App\OfficeUser;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesBusinesses;


class CaregiverExpirationsTest extends TestCase
{
    use RefreshDatabase, CreatesBusinesses;

    // protected $officeUser;

    public function setUp() : void
    {
        parent::setUp();

        $this->createBusinessWithUsers();

        $this->actingAs( $this->officeUser->user );
    }

    /**
     * @test
     */
    function an_invoice_can_be_generated_from_shifts()
    {
        CaregiverLicense::create([

            'caregiver_id' => $this->caregiver->id,
            'description'  => 'testing description',
            'name'         => 'testing name',
            'expires_at'   => now()->format( 'Y-m-d' ),
        ]);

        // probably could have used a factory, i just grabbed actual data from the network tab to test it directly
        $data = [
            [
                // 'id'                       => null,
                'type'                     => "No ID testing ( for custom expiration )",
                'chain_id'                 => 1,
                'created_at'               => "2019-09-03 20:44:08",
                'updated_at'               => "---",
                'chain_expiration_type_id' => '',
                'name'                     => "No ID testing ( for custom expiration )",
                'description'              => "will it work again",
                'expires_at'               => "09/24/2019",
                'expires_sort'             => ""
            ],
            // [
            //     'id'                       => null,
            //     'type'                     => "Drivers License",
            //     'chain_id'                 => 1,
            //     'created_at'               => "2019-09-03 20:44:08",
            //     'updated_at'               => "---",
            //     'chain_expiration_type_id' => 244,
            //     'name'                     => "Drivers License",
            //     'description'              => "",
            //     'expires_at'               => "",
            //     'expires_sort'             => ""
            // ],
            // [
            //     'id'                       => null,
            //     'type'                     => "Industry Standard",
            //     'chain_id'                 => 1,
            //     'created_at'               => "2019-09-03 20:44:34",
            //     'updated_at'               => "---",
            //     'chain_expiration_type_id' => 246,
            //     'name'                     => "Industry Standard",
            //     'description'              => "",
            //     'expires_at'               => "",
            //     'expires_sort'             => ""
            // ],
            // [
            //     'id'                       => null,
            //     'type'                     => "Olive Tree Chopper",
            //     'chain_id'                 => 1,
            //     'created_at'               => "2019-09-03 22:06:17",
            //     'updated_at'               => "---",
            //     'chain_expiration_type_id' => 247,
            //     'name'                     => "Olive Tree Chopper",
            //     'description'              => "",
            //     'expires_at'               => "",
            //     'expires_sort'             => ""
            // ],
            [
                'id'                       => null,
                'caregiver_id'             => 3,
                'name'                     => "assass",
                'description'              => "ffffff",
                'expires_at'               => '10/01/2019',
                'created_at'               => "2019-09-03 22:02:10",
                'updated_at'               => "09/04/2019 1:38 PM",
                'chain_expiration_type_id' => null,
                'expires_sort'             => "20191129"
            ],
            [
                'id'                       => null,
                'caregiver_id'             => 3,
                'name'                     => "ssdasexy",
                'description'              => "another test description",
                'expires_at'               => "10/01/2019",
                'created_at'               => "2019-09-03 22:02:39",
                'updated_at'               => "09/04/2019 1:38 PM",
                'chain_expiration_type_id' => null,
                'expires_sort'             => "20191001"
            ],
            [
                'id'                       => null,
                'caregiver_id'             => 3,
                'name'                     => "robert downy jr says hi",
                'description'              => "asdasdasd",
                'expires_at'               => "09/30/2019",
                'created_at'               => "2019-09-03 22:02:50",
                'updated_at'               => "09/04/2019 1:38 PM",
                'chain_expiration_type_id' => null,
                'expires_sort'             => "20190930"
            ],
            [
                'id'                       => null,
                'type'                     => "Visa Black Card",
                'chain_id'                 => 1,
                'created_at'               => "2019-09-03 20:44:16",
                'updated_at'               => "09/04/2019 1:58 PM",
                'chain_expiration_type_id' => 245,
                'name'                     => "Visa Black Card",
                'description'              => null,
                'expires_at'               => "09/24/2019",
                'expires_sort'             => "20190924"
            ],
            [
                'id'                       => null,
                'caregiver_id'             => 3,
                'name'                     => "asdasd",
                'description'              => null,
                'expires_at'               => "09/30/2019",
                'created_at'               => "2019-09-04 18:22:30",
                'updated_at'               => "09/04/2019 2:22 PM",
                'chain_expiration_type_id' => null,
                'expires_sort'             => "20190930"
            ],
            [
                'id'                       => 1,
                'type'                     => "Test",
                'chain_id'                 => 1,
                'created_at'               => "2019-08-20 12:52:14",
                'updated_at'               => "09/04/2019 2:26 PM",
                'chain_expiration_type_id' => 217,
                'name'                     => "Test",
                'description'              => "asd",
                'expires_at'               => "09/24/2019",
                'expires_sort'             => "20190924"
            ]
        ];
        $res = $this->post( route( 'business.caregivers.licenses.saveMany', [ 'caregiver' => $this->caregiver->id ] ), $data )
            ->assertSuccessful();

        // $res = CaregiverLicense::with( 'caregiver', 'caregiver.address' )->get();
    }
}
