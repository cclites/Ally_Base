<?php
namespace App\Services;

use App\CommunicationLog;
use Carbon\Carbon;
use Log;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

class PhoneService
{
    /**
     * @var \Twilio\Rest\Client
     */
    protected $client;

    /**
     * @var App\CommunicationLog
     */
    protected $log;

    /**
     * @var string
     */
    protected $from;

    /**
     * @var bool
     */
    protected $sandbox = false;

    /**
     * Maximum message length allowed.
     */
    const MAX_MESSAGE_LENGTH = 1600;

    /**
     * PhoneService constructor.
     * @param Client|null $client
     * @param bool $sandbox
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function __construct(?Client $client = null, bool $sandbox = false)
    {
        $this->from = config('services.twilio.default_number');

        if ($sandbox || config('sms.driver') == 'sandbox') {
            $this->sandbox = true;
            $this->client = $client ?: new Client(config('services.twilio-sandbox.sid'), config('services.twilio-sandbox.token'));
            $this->from = config('services.twilio-sandbox.default_number');
        } elseif (config('sms.driver') == 'twilio') {
            $this->client = $client ?: new Client(config('services.twilio.sid'), config('services.twilio.token'));
        } else {
            // an empty client will use the 'log' driver
        }
    }

    /**
     * Setter for from field.
     *
     * @param [type] $number
     * @return void
     */
    public function setFromNumber($number)
    {
        if ($this->sandbox) {
            $this->from = config('services.twilio-sandbox.default_number');
        } else {
            $this->from = $number;
        }
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
        if(strlen($message) > self::MAX_MESSAGE_LENGTH) {
            $message = substr($message, 0, self::MAX_MESSAGE_LENGTH);
        }

        try {
            $this->logCommunication($this->from, $to, $message);

            if (empty($this->client)) {
                return Log::info("Send Text Message to: {$to}\r\nFrom: {$this->from}\r\nBody: {$message}");
            }
            $message = $this->client->messages->create($to, ['from' => $this->from, 'body' => $message]);

            return $message->sid;
        } catch( TwilioException $ex ){

            if( strpos( $ex->getMessage(), 'blacklist') !== false ){
                // if this is a blacklist error..

                $this->log->update([ 'error' => 'Blacklisted Phone Number' ]);
            } elseif (strpos($ex->getMessage(), 'not a valid phone number') !== false) {
                //if this is not a valid phone number

                $this->log->update(['error' => 'Invalid phone number']);
            } else {
                // else pass along to log to sentry..

                app( 'sentry' )->captureException( $ex );
            }
        }
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
            $this->log = CommunicationLog::create([
                'body' => $message,
                'subject' => null,
                'to' => $to,
                'from' => $from,
                'sent_at' => Carbon::now(),
                'channel' => 'sms',
                'preview' => substr($message, 0, 100),
                'error' => null,
            ]);
        }
    }

}