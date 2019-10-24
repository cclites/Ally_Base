<?php

namespace App\Notifications;

use App\Business;
use App\Caregiver;
use App\CaregiverLicense;
use App\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LicenseExpirationReminder extends Notification
{
    use Queueable;

    /**
     * @var CaregiverLicense
     */
    protected $license;
    /**
     * @var Business
     */
    protected $business;

    /**
     * Create a new notification instance.
     *
     * @param Business $business
     * @param CaregiverLicense $license
     */
    public function __construct(Business $business, CaregiverLicense $license)
    {
        $this->license = $license;
        $this->business = $business;
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
        if($template = EmailTemplate::where('business_id', $this->business->id)->where('type', 'caregiver_expiration')->first()){
            return (new MailMessage)->markdown(
                'emails.caregiver.custom_expiration_reminder', [
                'business' => $this->business,
                'caregiver' => $this->license->caregiver,
                'license' => $this->license,
                'template' => $template
            ]);
        }else{
            return (new MailMessage)->markdown(
                'emails.caregiver.license_expiration_reminder', [
                'business' => $this->business,
                'caregiver' => $this->license->caregiver,
                'license' => $this->license
            ]);
        }


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
