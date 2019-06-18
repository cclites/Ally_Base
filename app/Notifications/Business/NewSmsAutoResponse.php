<?php

namespace App\Notifications\Business;

use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;
use App\SmsThreadReply;

class NewSmsAutoResponse extends BaseNotification
{
    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    const TITLE = '';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    const MESSAGE = '#MESSAGE#';

    /**
     * The action text.
     *
     * @var string
     */
    protected $action = 'View Text History';

    /**
     * The related SMS Reply.
     *
     * @var \App\SmsThreadReply
     */
    public $reply;
    public $newMessage;

    /**
     * Create a new notification instance.
     *
     * @var \App\SmsThreadReply $reply
     * @return void
     */
    public function __construct($reply, $message)
    {
        $this->reply = $reply;

        if (empty($this->reply->thread)) {
            $this->action = 'View Text History';
            $this->url = route('business.communication.sms-threads');
        } else {
            $this->action = 'View Thread';
            $this->url = route('business.communication.sms-threads.show', ['thread' => $this->reply->thread]);
        }
    }

    /**
     * get the notification's message.
     *
     * @return mixed|string
     */
    public function getMessage()
    {
        $message = str_replace('#MESSAGE#', $this->newMessage);
        return $message;
    }

    /**
     * Get the SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SendTextMessage
     * @throws \Exception
     */
    public function toSms($notifiable)
    {
        return $this->toSmsFromBusiness($notifiable, $this->reply->business);
    }

    /**
     * Get the SystemNotification representation of the notification.
     *
     * @param  mixed  $notifiable
     * @param  array  $data
     * @return \App\SystemNotification
     */
    public function toSystem($notifiable, $data = [])
    {
        return parent::toSystem($notifiable, [
            'reference_id' => $this->reply->id,
            'reference_type' => SmsThreadReply::class
        ]);
    }
}
