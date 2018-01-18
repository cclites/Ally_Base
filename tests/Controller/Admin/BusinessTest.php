<?php

namespace Tests\Controller\Admin;

use App\Admin;
use App\Business;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BusinessTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testAdminsCanUpdateBusinessContactInfo()
    {
        $admin = factory(Admin::class)->create();
        $this->actingAs($admin->user);

        $business = factory(Business::class)->create();
        $data = [
            'contact_name' => $this->faker->name,
            'contact_email' => $this->faker->safeEmail,
            'contact_phone' => $this->faker->phoneNumber
        ];
        $response = $this->put('/admin/businesses/'.$business->id.'/contact-info', $data);

        $response->assertStatus(200);

        $this->assertEquals($data['contact_name'], $business->fresh()->contact_name);
        $this->assertEquals($data['contact_email'], $business->fresh()->contact_email);
        $this->assertEquals($data['contact_phone'], $business->fresh()->contact_phone);

    }
}
