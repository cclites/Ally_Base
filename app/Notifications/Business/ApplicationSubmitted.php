<?php

namespace App\Notifications\Business;

use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;
use App\PhoneNumber;

class ApplicationSubmitted extends BaseNotification
{
    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    protected static $title = 'A Caregiver Application is Submitted';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    protected static $message = 'A new Caregiver Application was submitted.';

    /**
     * The related caregiver application.
     *
     * @var \App\CaregiverApplication
     */
    protected $application;

    /**
     * The action text.
     *
     * @var string
     */
    protected $action = 'View Application';

    /**
     * Create a new notification instance.
     *
     * @param \App\CaregiverApplication $application
     * @return void
     */
    public function __construct($application)
    {
        $this->application = $application;

        $this->url = route('business.caregivers.applications.show', ['application' => $this->application]);
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
        return $this->toSmsFromChain($notifiable, $this->application->businessChain);
    }
}
