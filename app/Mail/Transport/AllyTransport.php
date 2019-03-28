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
    protected function logCommunication(\Swift_Mime_SimpleMessage $message) : void
    {
        if (config('ally.communication_log')) {

            foreach (array_keys($message->getTo()) as $to) {
                CommunicationLog::create([
                    'body' => $message->getBody(),
                    'subject' => substr($message->getSubject(), 0, 255),
                    'to' => $to,
                    'from' => array_keys($message->getFrom())[0],
                    'sent_at' => Carbon::now(),
                    'channel' => 'mail',
                    'preview' => $this->getPreviewFromHtml($message->getBody()),
                ]);
            }
        }
    }

    protected function getPreviewFromHtml(?string $message)
    {
        if (empty($message)) {
            return '';
        }

        $message = strip_tags($this->strip_html_tags($message));
        $message = str_replace("\r", ' ', $message);
        $message = str_replace("\n", ' ', $message);
        return substr(trim($message), 0, 100);
    }

    protected function strip_html_tags( $text )
    {
        $text = preg_replace(
            array(
              // Remove invisible content
                '@<head[^>]*?>.*?</head>@siu',
                '@<style[^>]*?>.*?</style>@siu',
                '@<script[^>]*?.*?</script>@siu',
                '@<object[^>]*?.*?</object>@siu',
                '@<embed[^>]*?.*?</embed>@siu',
                '@<applet[^>]*?.*?</applet>@siu',
                '@<noframes[^>]*?.*?</noframes>@siu',
                '@<noscript[^>]*?.*?</noscript>@siu',
                '@<noembed[^>]*?.*?</noembed>@siu',
              // Add line breaks before and after blocks
                '@</?((address)|(blockquote)|(center)|(del))@iu',
                '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
                '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
                '@</?((table)|(th)|(td)|(caption))@iu',
                '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
                '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
                '@</?((frameset)|(frame)|(iframe))@iu',
            ),
            array(
                ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
                "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
                "\n\$0", "\n\$0",
            ),
            $text );
        return strip_tags( $text );
    }
}
