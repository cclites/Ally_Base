<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ChargePaymentNotification extends BaseNotification
{
    use Queueable;

    /**
     * @var App\User
     */
    public $recipient;

    /**
     * @var String
     */
    public $type;

    /**
     * @var string
     */
    public const KEY = "charge_payment_notification";

    public const TITLE = "Charge/Payment notifications";

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($recipient, $type)
    {
        $this->recipient = $recipient;
        $this->type = $type;
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
        $subject = $this->type === 'client' ? "Upcoming Senior Care Charge" : "Upcoming Senior Care Payment";

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.charge_payment_email', [
                'recipient' => $this->recipient,
                'type' => $this->type,
            ]);
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
            //
        ];
    }
}
