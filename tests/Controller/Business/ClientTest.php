<?php


namespace Tests\Controller\Business;

use App\Business;
use App\Client;
use App\OfficeUser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ClientTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $officeUser;

    public function setUp()
    {
        parent::setUp();

        $business = factory(Business::class)->create();
        $this->officeUser = factory(OfficeUser::class)->create();
        $this->actingAs($this->officeUser->user);
        $this->officeUser->businesses()->attach($business->id);
    }

    /** @test */
    public function an_office_user_can_update_client_hospital_info()
    {
        $client = factory(Client::class)->create(['business_id' => $this->officeUser->businesses()->first()->id]);

        $this->assertEquals(1, $this->officeUser->businesses()->first()->clients()->count());

        $name = "Test Hospital";
        $number = "1234567890";

        $client->update([
            'hospital_name' => $name,
            'hospital_number' => $number,
        ]);

        $client = $client->fresh();
        $this->assertEquals($name, $client->hospital_name);
        $this->assertEquals($number, $client->hospital_number);
    }
}