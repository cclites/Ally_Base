<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Client;
use App\Notifications\Business\ClientBirthday;

class CronDailyNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:daily_notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for daily event triggers and dispatches notifications.';

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
        // ======================================
        // OFFICE USER NOTIFICATIONS
        // ======================================
        
        $this->clientBirthdays();

        // ======================================
        // CAREGIVER NOTIFICATIONS
        // ======================================
        
    }

    /**
     * Check for Client birthdays that are today and notify the
     * related Office Users.
     *
     * @return void
     */
    public function clientBirthdays()
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
