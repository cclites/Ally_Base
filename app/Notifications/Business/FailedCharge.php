<?php

namespace App\Notifications\Business;

use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;

class FailedCharge extends BaseNotification
{
    const DISABLED = true;
    
    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    const TITLE = 'A Charge Fails';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    const MESSAGE = 'A charge has failed for Client #CLIENT#.';

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
        return str_replace('#CLIENT#', $this->client->name, static::MESSAGE);
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
        // TODO: handle failed charge system notification
        // return parent::toSystem($notifiable, [
        //     'reference_id' => ,
        //     'reference_type' => 
        // ]);
    }
}
