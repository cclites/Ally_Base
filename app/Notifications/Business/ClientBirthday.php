<?php

namespace App\Notifications\Business;

use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;
use App\PhoneNumber;

class ClientBirthday extends BaseNotification
{
    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    protected static $title = 'Client Birthday';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    protected static $message = 'Client #NAME# - birthday is today!';

    /**
     * The related client.
     *
     * @var \App\Client
     */
    protected $client;

    /**
     * The action text.
     *
     * @var string
     */
    protected $action = 'View Client Profile';

    /**
     * Create a new notification instance.
     *
     * @param \App\Client $client
     * @return void
     */
    public function __construct($client)
    {
        $this->client = $client;

        $this->url = route('business.clients.show', ['client' => $this->client]);
    }

    /**
     * Get the notification's message.
     *
     * @return string
     */
    public function getMessage()
    {
        return str_replace('#NAME#', $this->client->name, static::$message);
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
        return $this->toSmsFromBusiness($notifiable, $this->client->business);
    }
}
