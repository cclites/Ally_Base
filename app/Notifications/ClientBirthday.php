<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use App\PhoneNumber;
use App\Jobs\SendTextMessage;

class ClientBirthday extends BaseNotification
{
    /**
     * The unique key to identify the notification type.
     *
     * @var string
     */
    protected static $key = 'client_birthday';

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
     * The related business location.
     *
     * @var \App\Business
     */
    protected $business;

    /**
     * The action URL.
     *
     * @var string
     */
    protected $action;

    /**
     * Create a new notification instance.
     *
     * @param \App\Client $client
     * @return void
     */
    public function __construct($client)
    {
        $this->client = $client;

        $this->business = $this->client->business;

        $this->action = route('business.clients.show', ['client' => $this->client]);
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
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line($this->getMessage())
            ->action('View Client', $this->action);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => $this->getMessage(),
            'action' => $this->action,
        ];
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
        $number = new PhoneNumber();
        $number->input($notifiable->notification_phone);

        // send from the current business if an outgoing number set up
        if (empty($this->business->outgoing_sms_number)) {
            return null;
        }

        return new SendTextMessage(
            $number->number(false),
            $this->getMessage() . ' ' . $this->action,
            $this->business->outgoing_sms_number
        );
    }
}
