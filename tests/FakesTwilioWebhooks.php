<?php

namespace Tests;

use App\Caregiver;
use App\PhoneNumber;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Str;

trait FakesTwilioWebhooks
{
    /**
     * Generate twilio webhook data.
     *
     * @param string $to
     * @param string $from
     * @param string $message
     * @return array
     */
    public function generateWebhook(string $to, string $from, string $message)
    {
        return [
            'MessageSid' => Str::random(34),
            'AccountSid' => config('services.twilio.sid'),
            'MessagingServiceSid' => Str::random(34),
            'From' => PhoneNumber::formatE164($from),
            'To' => PhoneNumber::formatE164($to),
            'Body' => $message,
            'NumMedia' => 0,
        ];
    }

    /**
     * Submit POST request to the incoming SMS endpoint with a
     * fake generated twilio webhook.
     *
     * @param string $to
     * @param Caregiver $from
     * @param string $message
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function fakeWebhook($to = null, Caregiver $from = null, $message = null) : TestResponse
    {
        if (empty($to)) {
            $to = config('services.twilio.default_number');
        }

        if (empty($from)) {
            $from = "1234567890";
        } else {
            $from = $from->phoneNumbers()->first()->number(false);
        }

        if (empty($message)) {
            $message = Str::random(100);
        }

        $data = $this->generateWebhook($to, $from, $message);
        return $this->post(route('telefony.sms.incoming'), $data)
            ->assertStatus(200);
    }

}
