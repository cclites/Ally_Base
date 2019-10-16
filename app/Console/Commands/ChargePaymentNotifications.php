<?php

namespace App\Console\Commands;

use App\TriggeredReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Notifications\ChargePaymentNotification;
use App\User;
use App\Billing\Payment;
use App\Billing\Deposit;

class ChargePaymentNotifications extends Command{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:charge_payment_notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for daily charges and payments, and dispatches notifications.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $query = User::where('email', 'NOT LIKE', '%@allyms.com')
                 ->where('email', 'NOT LIKE', '%noemail%');

        $date = Carbon::now('America/New_York');
        $end = $date->setTimezone('UTC')->toDateTimeString();
        $start = $date->subHours(24)->setTimezone('UTC')->toDateTimeString();
        $expiration = $date->addHours(24)->setTimezone('UTC')->toDateTimeString();

        $clients = with(clone $query)->join('payments', 'payments.client_id', '=', 'users.id')
                    ->whereBetween('payments.created_at', [$start, $end])
                    ->get();

        $triggered = TriggeredReminder::getTriggered('charge_notification', $clients->pluck('id'));

        foreach ($clients as $client) {
            if ($triggered->contains($client->id)) {
                continue;
            }

            \Notification::send($client, new ChargePaymentNotification($client, 'client'));
            TriggeredReminder::markTriggered('charge_notification', $client->id, $expiration);
        }

        /* Handle Caregiver recipients*/
        $caregivers = with(clone $query)->join('deposits', 'deposits.caregiver_id', '=', 'users.id')
            ->whereBetween('deposits.created_at',  [$start, $end])
            ->get();

        $triggered = TriggeredReminder::getTriggered('payment_notification', $caregivers->pluck('id'));

        foreach ($caregivers as $caregiver) {
            if ($triggered->contains($caregiver->id)) {
                continue;
            }

            \Notification::send($caregiver, new ChargePaymentNotification($caregiver, 'caregiver'));
            TriggeredReminder::markTriggered('payment_notification', $caregiver->id, $expiration);
        }

    }
}