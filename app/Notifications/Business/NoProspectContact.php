<?php

namespace App\Notifications\Business;

use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;
use App\Prospect;

class NoProspectContact extends BaseNotification
{
    const DISABLED = true;
    
    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    const TITLE = 'A Prospect has no new prospect contact note for 14 days';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    const MESSAGE = 'Prospect #PROSPECT# has not been contacted in over 14 days.';

    /**
     * The action text.
     *
     * @var string
     */
    protected $action = 'View Prospect';

    /**
     * The related Prospect.
     *
     * @var \App\Prospect
     */
    protected $prospect;

    /**
     * Create a new notification instance.
     *
     * @var \App\Prospect $prospect
     * @return void
     */
    public function __construct($prospect)
    {
        parent::__construct();
        $this->prospect = $prospect;
        $this->url = route('business.prospects.show', ['prospect' => $this->prospect]);
    }

    /**
     * Get the notification's message.
     *
     * @return string
     */
    public function getMessage()
    {
        return str_replace('#PROSPECT#', $this->prospect->name, static::MESSAGE);
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
        return $this->toSmsFromBusiness($notifiable, $this->prospect->business);
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
            'reference_id' => $this->prospect->id,
            'reference_type' => Prospect::class
        ]);
    }
}
