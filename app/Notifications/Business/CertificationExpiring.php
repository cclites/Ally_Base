<?php

namespace App\Notifications\Business;

use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;

class CertificationExpiring extends BaseNotification
{
    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    const TITLE = '30 Days Before a Caregiver Certification Expires';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    const MESSAGE = 'Caregiver #CAREGIVER# - #CERTNAME# expires soon.';

    /**
     * The action text.
     *
     * @var string
     */
    protected $action = 'View Caregiver Profile';

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
        parent::__construct();
        $this->license = $license;
        $this->url = route('business.caregivers.show', ['caregiver' => $this->license->caregiver]);
    }

    /**
     * Get the notification's message.
     *
     * @return string
     */
    public function getMessage()
    {
        $message = str_replace('#CAREGIVER#', $this->license->caregiver->name, static::MESSAGE);
        $message = str_replace('#CERTNAME#', $this->license->name, $message);
        return $message;
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
        $business = optional($notifiable->role->businesses)->first();

        if (empty($business)) {
            return false;
        }

        return $this->toSmsFromBusiness($notifiable, $business);
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
            'reference_id' => $this->license->id,
            'reference_type' => CaregiverLicense::class
        ]);
    }
}
