<?php
namespace Tests\Controller\Telefony;

use App\Business;
use App\Caregiver;
use App\Client;
use App\PhoneNumber;
use App\Schedule;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelefonyBase extends TestCase
{
    use RefreshDatabase;

    /**
     * @var \App\Business
     */
    public $business;

    /**
     * @var \App\Caregiver
     */
    public $caregiver;

    /**
     * @var \App\Client
     */
    public $client;

    /**
     * @var \App\PhoneNumber
     */
    public $phone;

    public function setUp()
    {
        parent::setUp();
        $this->business = factory(Business::class)->create();
        $this->client = factory(Client::class)->create(['business_id' => $this->business->id, 'client_type' => 'private_pay']);
        $this->caregiver = factory(Caregiver::class)->create();
        $this->phone = factory(PhoneNumber::class)->make(['national_number' => '1234567890']);
        $this->client->phoneNumbers()->save($this->phone);
        $this->client->caregivers()->attach($this->caregiver);
    }

    protected function assertXmlHeader($response)
    {
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
    }

    protected function telefonyGet($url, $parameters = [], $headers = [])
    {
        $url = rtrim('/api/telefony/' . $url, '/');
        $parameters += ['From' => '1234567890'];
        $headers += ['Content-Type' => 'text/xml'];
        $url = $url . '?' . http_build_query($parameters);
        return $this->get($url, $headers);
    }

    protected function telefonyPost($url, $parameters = [], $headers = [])
    {
        $url = rtrim('/api/telefony/' . $url, '/');
        $parameters += ['From' => '1234567890'];
        $headers += ['Content-Type' => 'text/xml'];
        return $this->post($url, $parameters, $headers);
    }

    /**
     * @param array $attributes
     * @return \App\Schedule
     */
    protected function createSchedule($attributes = []) {
        $attributes += [
            'business_id' => $this->business->id,
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'starts_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'weekday' => Carbon::now()->dayOfWeek,
            'duration' => 60,
        ];
        return Schedule::create($attributes);
    }

    /**
     * @param array $attributes
     * @return \App\Shift
     */
    protected function createShift($attributes = []) {
        $attributes += [
            'business_id' => $this->business->id,
            'client_id' => $this->client->id,
            'caregiver_id' => $this->caregiver->id,
            'checked_in_method' => Shift::METHOD_TELEPHONY,
            'checked_in_time' => Carbon::now()->subHour(),
            'checked_in_number' => '1234567890',
            'checked_in_latitude' => null,
            'checked_in_longitude' => null,
            'checked_out_time' => null,
            'checked_out_number' => null,
            'checked_out_latitude' => null,
            'checked_out_longitude' => null,
            'status' => Shift::CLOCKED_IN,
        ];
        return factory(Shift::class)->create($attributes);
    }
}
