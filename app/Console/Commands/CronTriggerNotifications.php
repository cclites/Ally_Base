<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Client;
use App\Notifications\Business\ClientBirthday;

class CronTriggerNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:trigger_notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for all events that trigger notifications.';

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
        $this->handleClientBirthdays();
    }

    /**
     * Check for Client birthdays that are today and notify the
     * related Office Users.
     *
     * @return void
     */
    public function handleClientBirthdays()
    {
        $clients = Client::whereHas('user', function ($q) {
            $today = date('m-d');
            $q->where('date_of_birth', 'like', "%-$today");
        })->get();

        foreach ($clients as $client) {
            $users = $client->business->usersToNotify(ClientBirthday::class);
            \Notification::send($users, new ClientBirthday($client));
        }
    }
}
