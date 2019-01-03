<?php

namespace App\Notifications\Business;

use App\Notifications\BaseNotification;

class BiAnualVisitDue extends BaseNotification
{
    public static $disabled = true;
    
    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    protected static $title = 'Client Bi-Annual Visits are Due';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    protected static $message = 'Client #CLIENT# bi-annual visit due.';

    /**
     * The action text.
     *
     * @var string
     */
    protected $action = 'View Client Profile';

    /**
     * The related Client.
     *
     * @var \App\Client
     */
    protected $client;

    /**
     * Create a new notification instance.
     *
     * @var \App\Client $client
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
        return str_replace('#CLIENT#', $this->client->name, static::$message);
    }

    /**
     * Get the SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \App\Jobs\SendTextMessage
     * @throws \Exception
     */
    public function toSms($notifiable)
    {
        return $this->toSmsFromBusiness($notifiable, $this->client->business);
    }
}
