<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use App\PhoneNumber;
use App\Jobs\SendTextMessage;

class ApplicationSubmitted extends BaseNotification
{
    /**
     * The unique key to identify the notification type.
     *
     * @var string
     */
    protected static $key = 'new_application';

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
     * The related business chain.
     *
     * @var \App\BusinessChain
     */
    protected $businessChain;

    /**
     * The action URL.
     *
     * @var string
     */
    protected $action;

    /**
     * Create a new notification instance.
     *
     * @param \App\CaregiverApplication $application
     * @return void
     */
    public function __construct($application)
    {
        $this->application = $application;

        $this->businessChain = $application->businessChain;

        $this->action = route('business.caregivers.applications.show', ['application' => $this->application]);
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
            ->action('View Application', $this->action);
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

        // send from the first business on the chain that has an outgoing number set up
        $business = $this->businessChain->businesses()->whereNotNull('outgoing_sms_number')->first();
        if (empty($business)) {
            return null;
        }

        return new SendTextMessage(
            $number->number(false),
            $this->getMessage() . ' ' . $this->action,
            $business->outgoing_sms_number
        );
    }
}
