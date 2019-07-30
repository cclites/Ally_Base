<?php

namespace App\Notifications\Business;

use App\Notifications\BaseNotification;
use App\Jobs\SendTextMessage;
use App\Shift;

class UnverifiedShift extends BaseNotification
{
    /**
     * The label of the notification (used for preferences).
     *
     * @var string
     */
    const TITLE = 'An Unverified Clock-In or Clock-Out Occurs';

    /**
     * The template for the message to transmit.
     *
     * @var string
     */
    const MESSAGE = 'Caregiver #CAREGIVER# an unverified clock-#MODE# for Client #CLIENT# #TIMESTAMP#.';

    /**
     * Clock in or out.
     *
     * @var string
     */
    protected $mode;

    /**
     * The related shift.
     *
     * @var \App\Shift
     */
    public $shift;

    /**
     * The action text.
     *
     * @var string
     */
    protected $action = 'View Shift';

    /**
     * Create a new notification instance.
     *
     * @var \App\Caregiver $caregiver
     * @var \App\Schedule $schedule
     * @return void
     */
    public function __construct($shift)
    {
        parent::__construct();
        $this->shift = $shift;
        $this->mode = empty($this->shift->checked_out_time) ? 'in' : 'out';
        $this->url = route('business.shifts.show', ['shift' => $this->shift]);
    }

    /**
     * Get the notification's message.
     *
     * @return string
     */
    public function getMessage()
    {
        $checked_in_time = local_date($this->shift->checked_in_time, 'm/d/Y g:iA', $this->shift->business->timezone);
        $checked_out_time = local_date($this->shift->checked_out_time, 'm/d/Y g:iA', $this->shift->business->timezone);
        
        $message = str_replace('#CAREGIVER#', $this->shift->caregiver->name(), static::MESSAGE);
        $message = str_replace('#CLIENT#', $this->shift->client->name(), $message);
        $message = str_replace('#MODE#', $this->mode, $message);
        return str_replace('#TIMESTAMP#', $checked_in_time, $message);
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
        return $this->toSmsFromBusiness($notifiable, $this->shift->business);
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
            'reference_id' => $this->shift->id,
            'reference_type' => Shift::class
        ]);
    }
}
