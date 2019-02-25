<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Caregiver;
use App\BusinessChain;

class CaregiverWelcomeEmail extends Notification
{
    use Queueable;

    /**
     * @var \App\Caregiver
     */
    public $caregiver;

    /**
     * @var \App\BusinessChain
     */
    public $businessChain;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Caregiver $caregiver, BusinessChain $businessChain)
    {
        $this->caregiver = $caregiver;
        $this->businessChain = $businessChain;
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
            ->subject('Welcome to Ally')
            ->markdown('emails.caregiver.welcome', [
                'caregiver' => $this->caregiver,
                'businessChain' => $this->businessChain,
                'url' => $this->caregiver->setup_url,
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
