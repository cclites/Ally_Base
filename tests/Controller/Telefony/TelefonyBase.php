<?php
namespace Tests\Controller\Telefony;

use App\Business;
use App\Caregiver;
use App\Client;
use App\PhoneNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelefonyBase extends TestCase
{
    use RefreshDatabase;

    public $business;
    public $caregiver;
    public $client;
    public $phone;

    public function setUp()
    {
        parent::setUp();
        $this->business = factory(Business::class)->create();
        $this->client = factory(Client::class)->create(['business_id' => $this->business->id]);
        $this->caregiver = factory(Caregiver::class)->create();
        $this->phone = factory(PhoneNumber::class)->make(['national_number' => '1234567890']);
        $this->client->phoneNumbers()->save($this->phone);
    }

    protected function assertXmlHeader($response)
    {
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
    }

    protected function telefonyGet($url, $parameters = [], $headers = [])
    {
        $parameters += ['From' => '1234567890'];
        $headers += ['Content-Type' => 'text/xml'];
        $url = $url . '?' . http_build_query($parameters);
        return $this->get($url, $headers);
    }

    protected function telefonyPost($url, $parameters = [], $headers = [])
    {
        $parameters += ['From' => '1234567890'];
        $headers += ['Content-Type' => 'text/xml'];
        return $this->post($url, $parameters, $headers);
    }
}