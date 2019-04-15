<?php

namespace App\Notifications\Caregiver;

use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;
use App\SystemNotification;
use App\CaregiverLicense;

class CertificationExpired extends BaseNotification
{
    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    const TITLE = 'One of my Certifications Expires';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    const MESSAGE = 'Your #CERTNAME# has expired!';

    /**
     * The action text.
     *
     * @var string
     */
    protected $action = 'Login';

    /**
     * The related license.
     *
     * @var \App\CaregiverLicense
     */
    public $license;

    /**
     * Create a new notification instance.
     *
     * @var \App\CaregiverLicense $license
     * @return void
     */
    public function __construct($license)
    {
        $this->license = $license;
        $this->url = route('home');
    }

    /**
     * Get the notification's message.
     *
     * @return string
     */
    public function getMessage()
    {
        return str_replace('#CERTNAME#', $this->license->name, static::MESSAGE);
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
        $business = optional($this->license->caregiver->businesses)->first();

        if (empty($business)) {
            return false;
        }

        return $this->toSmsFromBusiness($notifiable, $business);
    }
}
