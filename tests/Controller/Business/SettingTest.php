<?php

namespace Tests\Controller\Business;

use App\Business;
use App\BusinessChain;
use App\Caregiver;
use App\Client;
use App\OfficeUser;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
        $this->disableExceptionHandling();

        $chain = factory(BusinessChain::class)->create();
        $business = factory(Business::class)->create(['chain_id' => $chain->id]);
        $this->officeUser = factory(OfficeUser::class)->create(['chain_id' => $chain->id]);
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
        $this->expectException(HttpException::class);

        $client = factory(Client::class)->create();
        $this->actingAs($client->user);

        $this->get(route('business.settings.index'))
            ->assertStatus(403);
    }

    public function testACaregiverCanNotSeeTheBusinessSettingsPage()
    {
        $this->expectException(HttpException::class);

        $caregiver = factory(Caregiver::class)->create();
        $this->actingAs($caregiver->user);

        $this->get(route('business.settings.index'))
            ->assertStatus(403);
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
