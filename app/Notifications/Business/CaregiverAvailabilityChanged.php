<?php

namespace App\Notifications\Business;

use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Jobs\SendTextMessage;
use App\Caregiver;

class CaregiverAvailabilityChanged extends BaseNotification
{
    use Queueable;

    public $caregiver;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Caregiver $caregiver)
    {
        \Log::info("Sending Caregiver notification");
        $this->caregiver = $caregiver;
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
                    ->line($this->caregiver->name . " has marked themselves as unavailable for scheduled shifts.")
                    ->action('See affected schedules', url('/business/reports/caregiver-availability-conflict/' . $this->caregiver->id));
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
