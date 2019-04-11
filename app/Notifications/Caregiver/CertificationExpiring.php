<?php

namespace App\Notifications\Caregiver;

use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;

class CertificationExpiring extends BaseNotification
{
    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    protected static $title = '30 Days Before one of my Certification Expires';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    protected static $message = 'Your #CERTNAME# expires soon.';

    /**
     * The action text.
     *
     * @var string
     */
    protected $action = 'Login';

    /**
     * The number of days in which the Caregiver should start to
     * receive 'expiring' reminders.
     *
     * @var integer
     */
    public static $threshold = 30;

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
