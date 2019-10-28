<?php

namespace App\Mail\CronResults;

use App\Console\Commands\CronChargePaymentNotifications;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailable;

class ChargePaymentNotificationResults extends Mailable
{
    use SerializesModels;

    /**
     * Instance of the CRON including it's results.
     *
     * @var CronChargePaymentNotifications
     */
    protected $cron;

    /**
     * Create a new message instance.
     *
     * @param CronChargePaymentNotifications $cron
     */
    public function __construct(CronChargePaymentNotifications $cron)
    {
        $this->cron = $cron;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject('Ally CRON Results - Charge/Payment Notifications');

        return $this->markdown('emails.cron-results.charge-payment-notification', [
            'clients' => $this->cron->clients,
            'caregivers' => $this->cron->caregivers,
            'total' => $this->cron->clients->count() + $this->cron->caregivers->count(),
            'errors' => $this->cron->errors,
            'log' => $this->cron->cronLog,
        ]);
    }
}
