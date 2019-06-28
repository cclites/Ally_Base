<?php

namespace App\Notifications\Caregiver;

use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;

class VisitAccuracyCheck extends BaseNotification
{
    const DISABLED = true;

    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    const TITLE = 'Visit Accuracy Check - Friendly Reminder to check visits for accuracy, sent every Monday at 1pm Eastern';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    const MESSAGE = 'Please check your visits for accuracy.  We are preparing for charges which affect your deposit later this week.';

    /**
     * The action text.
     *
     * @var string
     */
    protected $action = 'Login';

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->url = route('home');
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
}
