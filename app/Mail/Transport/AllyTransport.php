<?php
namespace App\Mail\Transport;

use Swift_Mime_Message;
use App\Emails;
use Swift_SmtpTransport as SmtpTransport;

class AllyTransport extends SmtpTransport
{
    public function send(\Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        \Log::info(json_encode([
            'body'    => $message->getBody(),
            'to'      => implode(',', array_keys($message->getTo())),
            'subject' => $message->getSubject()
        ]));

        parent::send($message, $failedRecipients);
    }
}
