<?php
namespace Tests\Controller;

use App\Client;
use App\Caregiver;
use App\OfficeUser;
use Illuminate\Support\Facades\Log;
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

        // Log exceptions to stderr
        \Config::set('logging.default', 'stderr');

        $this->client = factory(Client::class)->create();
    }

    public function testClientProfileResponse()
    {
        $this->actingAs($this->client->user);
        $response = $this->get('/profile');
        $response->assertStatus(200);
        $response->assertSeeText(htmlentities($this->client->lastname, ENT_QUOTES));
    }
}
