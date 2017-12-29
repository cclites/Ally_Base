<?php
namespace Tests\Controller;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CaregiverCallInTest extends TestCase
{
    public function testCanGetTwilioGreeting()
    {
        $response = $this->get('/api/caregiver/greeting');
        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
        $response->assertSee('<Say>Hello');
        $response->assertSee('Press 1 to check in. Press 2 to check out.');
    }

    public function testSeeCheckInTwilioResponse()
    {
        $response = $this->post('/api/caregiver/check-in-or-out', ['Digits' => 1]);
        $response->assertSuccessful();
        $response->assertSee('checking in');
    }

    public function testSeeCheckOutTwilioResponse()
    {
        $response = $this->post('/api/caregiver/check-in-or-out', ['Digits' => 2]);
        $response->assertSuccessful();
        $response->assertSee('checking out');
    }

    public function testSeeBadSelectionTwilioResponse()
    {
        $response = $this->post('/api/caregiver/check-in-or-out', ['Digits' => 3]);
        $response->assertSuccessful();
        $response->assertSee('Returning to the main menu');
    }

    public function testSeeCheckedInResponse()
    {
        $response = $this->post('/api/caregiver/check-in', ['Digits' => 1]);
        $response->assertSuccessful();
        $response->assertSee('You have checked in');
    }

    /**
     * @depends testSeeCheckedInResponse
     */
    public function testStartShift()
    {
        $this->assertDatabaseHas('shifts', [
            'caregiver_id' => 3,
            'checked_out_time' => null,
        ]);
    }

    public function testSeeEnterCaregiverIdResponse()
    {
        $response = $this->post('/api/caregiver/check-in', ['Digits' => 2]);
        $response->assertSuccessful();
        $response->assertSee('enter your caregiver ID');
    }
}
