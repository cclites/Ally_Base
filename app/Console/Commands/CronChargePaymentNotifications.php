<?php

namespace App\Console\Commands;

use App\Mail\CronResults\ChargePaymentNotificationResults;
use App\Notifications\ChargePaymentNotification;
use Illuminate\Support\Collection;
use Illuminate\Console\Command;
use App\TriggeredReminder;
use Carbon\Carbon;
use App\Caregiver;
use App\Client;

class CronChargePaymentNotifications extends Command
{
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
     * A list of errors occurred while processing.
     *
     * @var Collection
     */
    public $errors;

    /**
     * Clients that have been sent notifications.
     *
     * @var Collection
     */
    public $clients;

    /**
     * Caregivers that have been sent notifications.
     *
     * @var Collection
     */
    public $caregivers;

    /**
     * Log of output messages.
     *
     * @var Collection
     */
    public $cronLog;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->cronLog = collect([]);
        $this->clients = collect([]);
        $this->caregivers = collect([]);
        $this->errors = collect([]);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Throwable
     */
    public function handle()
    {
        try {
            $this->sendPaymentNotifications();
        }
        catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            $this->errors->push("A fatal error occurred: {$ex->getMessage()}");
            $this->log("Fatal error, exiting");
        }

        $this->dispatchResultsEmail(config('ally.cron_results_to'));

        return 0;
    }

    /**
     * Send notifications to clients and caregivers that have had charges/payments.
     */
    protected function sendPaymentNotifications()
    {
        $date = Carbon::now('America/New_York');
        $end = $date->setTimezone('UTC')->toDateTimeString();
        $start = $date->subHours(24)->setTimezone('UTC')->toDateTimeString();
        $expiration = $date->addHours(24)->subMinutes(1)->setTimezone('UTC')->toDateTimeString();

        $this->log("Searching for clients with recent payments...");
        $clients = $this->getMatchingClients([$start, $end]);
        $this->log("Found a total of {$clients->count()} clients with recent payments.");

        $triggered = TriggeredReminder::getTriggered(ChargePaymentNotification::getKey(), $clients->pluck('id'));
        foreach ($clients as $client) {
            if ($triggered->contains($client->id)) {
                $this->log("Client {$client->name} has already been sent a notification for this charge.");
                continue;
            }

            $this->log("Sending client {$client->name} a notification...");
            \Notification::send($client->user, new ChargePaymentNotification($client, $client->role_type));
            TriggeredReminder::markTriggered(ChargePaymentNotification::getKey(), $client->id, $expiration);
            $this->clients->push([
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
            ]);
            $this->log("Notification sent.");
            sleep(1);
        }

        /* Handle Caregiver recipients*/
        $this->log("Searching for caregivers with recent deposits...");
        $caregivers = $this->getMatchingCaregivers([$start, $end]);
        $this->log("Found a total of {$caregivers->count()} caregivers with recent deposits.");

        $triggered = TriggeredReminder::getTriggered(ChargePaymentNotification::getKey(), $caregivers->pluck('id'));
        foreach ($caregivers as $caregiver) {
            if ($triggered->contains($caregiver->id)) {
                $this->log("Caregiver {$caregiver->name} has already been sent a notification for this deposit.");
                continue;
            }

            $this->log("Sending caregiver {$caregiver->name} a notification...");
            \Notification::send($caregiver->user, new ChargePaymentNotification($caregiver, $caregivers->type));
            TriggeredReminder::markTriggered(ChargePaymentNotification::getKey(), $caregiver->id, $expiration);
            $this->caregivers->push([
                'id' => $caregiver->id,
                'name' => $caregiver->name,
                'email' => $caregiver->email,
            ]);
            $this->log("Notification sent.");
            sleep(1);
        }

        $this->log("Operation complete.");
    }

    /**
     * Get clients with recent payments.
     *
     * @param array $dateRange
     * @return Collection
     */
    public function getMatchingClients(array $dateRange) : Collection
    {
        return Client::whereHas('user', function ($q) {
            $q->whereNotNull('email')
                ->where('email', 'NOT LIKE', '%@allyms.com')
                ->where('email', 'NOT LIKE', '%noemail%');
        })->whereHas('payments', function ($q) use ($dateRange) {
            $q->whereBetween('created_at', $dateRange);
        })
            ->with('user')
            ->get();
    }

    /**
     * Get Caregivers with recent deposits.
     *
     * @param array $dateRange
     * @return Collection
     */
    public function getMatchingCaregivers(array $dateRange) : Collection
    {
        return Caregiver::whereHas('user', function ($q) {
            $q->whereNotNull('email')
                ->where('email', 'NOT LIKE', '%@allyms.com')
                ->where('email', 'NOT LIKE', '%noemail%');
        })->whereHas('deposits', function ($q) use ($dateRange) {
            $q->whereBetween('created_at', $dateRange);
        })
            ->with('user')
            ->get();
    }

    /**
     * Send email with CRON results.
     *
     * @param string|null $email
     * @throws \Throwable
     */
    protected function dispatchResultsEmail(?string $email) : void
    {
        if (empty($email)) {
            return;
        }

        \Mail::to($email)
            ->send(new ChargePaymentNotificationResults($this));
    }

    /**
     * Output message to the console and save to the log.
     *
     * @param string $message
     */
    protected function log(string $message) : void
    {
        $this->cronLog->push($message);
        $this->info($message);
    }
}