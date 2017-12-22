<?php

namespace App\Notifications;

use App\Business;
use App\Client;
use App\Confirmations\Confirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ClientConfirmation extends Notification
{
    use Queueable;

    protected $business;
    protected $token;
    protected $url;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Client $client, Business $business)
    {
        $this->business = $business;
        $confirmation = new Confirmation($client);
        $this->token = $confirmation->getToken();
        $this->url = route('confirm.client', [$this->token]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
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
            ->subject('Please confirm your information for ' . $this->business->name)
            ->markdown('emails.client.verification', [
                'business' => $this->business,
                'url' => $this->url
            ]);
    }
}
