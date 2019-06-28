<?php

namespace App\Notifications\Business;

use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;
use App\Timesheet;

class ManualTimesheet extends BaseNotification
{
    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    const TITLE = 'A Caregiver Submits a Manual Timesheet';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    const MESSAGE = 'Caregiver #CAREGIVER# has submitted a system timesheet.';

    /**
     * The action text.
     *
     * @var string
     */
    protected $action = 'View Timesheet';

    /**
     * The related timesheet.
     *
     * @var \App\Timesheet
     */
    public $timesheet;

    /**
     * Create a new notification instance.
     *
     * @var \App\Timesheet $timesheet
     * @return void
     */
    public function __construct($timesheet)
    {
        parent::__construct();
        $this->timesheet = $timesheet;
        $this->url = route('business.timesheet', ['timesheet' => $this->timesheet]);
    }

    /**
     * Get the notification's message.
     *
     * @return string
     */
    public function getMessage()
    {
        return str_replace('#CAREGIVER#', $this->timesheet->caregiver->name, static::MESSAGE);
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
        return $this->toSmsFromBusiness($notifiable, $this->timesheet->business);
    }

    /**
     * Get the SystemNotification representation of the notification.
     *
     * @param mixed $notifiable
     * @param array $data
     * @return \App\SystemNotification
     */
    public function toSystem($notifiable, $data = [])
    {
        return parent::toSystem($notifiable, [
            'reference_id' => $this->timesheet->id,
            'reference_type' => Timesheet::class
        ]);
    }
}
