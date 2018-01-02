<?php
namespace Tests\Controller;

use App\Client;
use App\Caregiver;
use App\OfficeUser;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected $client;
    protected $caregiver;
    protected $officeUser;

    public function setUp()
    {
        parent::setUp();

        $this->client = factory(Client::class)->create();
        $this->caregiver = factory(Caregiver::class)->create();
        $this->officeUser = factory(OfficeUser::class)->create();
    }

    public function testClientProfileResponse()
    {
        $this->actingAs($this->client->user);
        $response = $this->get('/profile');
        $response->assertStatus(200);
        $response->assertSeeText($this->client->lastname);
    }
}
