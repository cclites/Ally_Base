<?php

namespace App\Notifications\Business;

use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;
use App\Client;

class ClientBirthday extends BaseNotification
{
    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    const TITLE = 'Client Birthday';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    const MESSAGE = 'Client #NAME# - birthday is today!';

    /**
     * The related client.
     *
     * @var \App\Client
     */
    public $client;

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
        parent::__construct();
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
        return str_replace('#NAME#', $this->client->name, static::MESSAGE);
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

    /**
     * Get the SystemNotification representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SystemNotification
     */
    public function toSystem($notifiable, $data = [])
    {
        return parent::toSystem($notifiable, [
            'reference_id' => $this->client->id,
            'reference_type' => Client::class
        ]);
    }
}
