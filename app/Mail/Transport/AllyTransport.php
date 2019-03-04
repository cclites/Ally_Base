<?php
namespace App\Mail\Transport;

use App\CommunicationLog;
use Carbon\Carbon;
use Swift_Mime_Message;
use App\Emails;
use Swift_SmtpTransport as SmtpTransport;

class AllyTransport extends SmtpTransport
{
    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param \Swift_Mime_SimpleMessage $message
     * @param string[] $failedRecipients An array of failures by-reference
     *
     * @return int
     */
    public function send(\Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->logCommunication($message);

        if (config('app.env') != 'testing') {
            // ally mail driver automatically prevents sending actual
            // main in a testing environment.
            return parent::send($message, $failedRecipients);
        }

        return 0;
    }

    /**
     * Log the outgoing message to the database.
     *
     * @param \Swift_Mime_SimpleMessage $message
     */
    public function logCommunication(\Swift_Mime_SimpleMessage $message) : void
    {
        if (config('ally.communication_log')) {
            foreach (array_keys($message->getTo()) as $to) {
                CommunicationLog::create([
                    'business_id' => null, // cannot track business for mail at this point
                    'body' => $message->getBody(),
                    'subject' => substr($message->getSubject(), 0, 255),
                    'to' => $to,
                    'from' => array_keys($message->getFrom())[0],
                    'sent_at' => Carbon::now(),
                    'channel' => 'mail',
                ]);
            }
        }
    }
}
