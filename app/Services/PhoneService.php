<?php
namespace App\Services;

use App\CommunicationLog;
use Carbon\Carbon;
use Log;
use Twilio\Rest\Client;

class PhoneService
{
    /**
     * @var \Twilio\Rest\Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $from;

    public function __construct(Client $client = null)
    {
        if (config('sms.driver') == 'twilio') {
            $this->client = $client ?: new Client(config('services.twilio.sid'), config('services.twilio.token'));
        } else {
            // an empty client will use the 'log' driver
        }
        $this->from = config('services.twilio.default_number');
    }

    /**
     * Setter for from field.
     *
     * @param [type] $number
     * @return void
     */
    public function setFromNumber($number)
    {
        $this->from = $number;
    }

    /**
     * Send a text message and return the message SID
     *
     * @param $to
     * @param $message
     * @return mixed
     */
    public function sendTextMessage($to, $message)
    {
        $this->logCommunication($this->from, $to, $message);

        if (empty($this->client)) {
            return Log::info("Send Text Message to: {$to}\r\nFrom: {$this->from}\r\nBody: {$message}");
        }

        $message = $this->client->messages->create($to, ['from' => $this->from, 'body' => $message]);

        return $message->sid;
    }

    /**
     * Log the outgoing message to the database.
     *
     * @param string $from
     * @param string $to
     * @param string $message
     */
    public function logCommunication(string $from, string $to, string $message) : void
    {
        if (config('ally.communication_log')) {
            CommunicationLog::create([
                'body' => $message,
                'subject' => null,
                'to' => $to,
                'from' => $from,
                'sent_at' => Carbon::now(),
                'channel' => 'sms',
                'preview' => substr($message, 0, 100),
            ]);
        }
    }
}