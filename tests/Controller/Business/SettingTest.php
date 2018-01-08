<?php

namespace Tests\Controller\Business;

use App\Business;
use App\Caregiver;
use App\Client;
use App\OfficeUser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class SettingTest extends TestCase
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

    /**
     * Check that the Business Settings page works for an office user
     * @test
     * @return void
     */
    public function testAnOfficeUserCanSeeTheBusinessSettingsPage()
    {
        $this->actingAs($this->officeUser->user);

        $response = $this->get('/business/settings');
        $response->assertStatus(200);
        $response->assertSeeText('Provider Settings');
    }

    /**
     * @test
     * @return void
    */
    public function testAClientCanNotSeeTheBusinessSettingsPage()
    {
        $client = factory(Client::class)->create();
        $this->actingAs($client->user);
        $response = $this->get('/business/settings');

        $response->assertStatus(403);
    }

    public function testACaregiverCanNotSeeTheBusinessSettingsPage()
    {
        $caregiver = factory(Caregiver::class)->create();
        $this->actingAs($caregiver->user);
        $response = $this->get('/business/settings');

        $response->assertStatus(403);
    }

    public function testAnOfficeUserCanUpdateBusinessSettings()
    {
        $this->actingAs($this->officeUser->user);

        $data = [
            'scheduling' => false,
            'mileage_rate' => $this->faker->numberBetween(0, 200),
            'calendar_default_view' => 'week',
            'calendar_caregiver_filter' => 'all',
            'phone1' => $this->faker->phoneNumber,
            'phone2' => $this->faker->phoneNumber,
            'address1' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'state' => str_random(2),
            'zip' => $this->faker->postcode
        ];

        $business = Business::inRandomOrder()->first();

        $business->update($data);

        $this->assertEquals($data['mileage_rate'], $business->fresh()->mileage_rate);
        $this->assertEquals($data['phone1'], $business->fresh()->phone1);
        $this->assertEquals($data['phone2'], $business->fresh()->phone2);
        $this->assertEquals($data['address1'], $business->fresh()->address1);
    }

}
