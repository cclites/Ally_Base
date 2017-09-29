<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CaregiverCallInTest extends TestCase
{
    public function testCanGetTwilioGreeting()
    {
        $response = $this->get('/api/caregiver/greeting');
        $response->assertSee('<Say>Hello');
    }
}
