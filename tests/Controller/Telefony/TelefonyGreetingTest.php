<?php
namespace Tests\Controller\Telefony;

class TelefonyGreetingTest extends TelefonyBase
{
    public function test_backwards_compatibility()
    {
        $response = $this->get('/api/caregiver/greeting');
        $response->assertRedirect('/api/telefony');
    }

    public function test_403_error_without_a_from_number()
    {
        $response = $this->get('/api/telefony', ['Content-Type' => 'text/xml']);
        $response->assertStatus(403);
    }

    public function test_403_error_without_xml_request()
    {
        $response = $this->get('/api/telefony?From=1234567890');
        $response->assertStatus(403);
    }

    public function test_number_mismatch_response()
    {
        $response = $this->telefonyGet('/api/telefony', ['From' => '5555555555']);
        $response->assertSee('<Say voice="alice" loop="1">The number you are calling from is not recognized.  The phone number needs to be linked to the client account for verification purposes.</Say>');
    }

    public function test_greeting_tells_user_to_clock_in_or_out()
    {
        $response = $this->telefonyGet('/api/telefony');
        $this->assertXmlHeader($response);
        $response->assertStatus(200);
        $response->assertSee('<Say');
        $response->assertSee('Press 1 to clock in .. Press 2 to clock out.');
    }

}