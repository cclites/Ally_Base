<?php

namespace App\Console\Commands;

use App\Business;
use App\Caregiver;
use App\Client;
use App\Notifications\CaregiverWelcomeEmail;
use App\Notifications\ClientWelcomeEmail;
use App\Notifications\TrainingEmail;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendWelcomeEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:welcome-emails {business}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send client/caregiver welcome and training emails to all users off a specific business.';

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
        $business = Business::findOrFail($this->argument('business'));

        $userType = $this->choice("Which type of {$business->name}'s users would you like to send emails to?", [
            'Active Caregivers',
            'Active Clients',
            'ALL Caregivers',
            'ALL Clients',
        ], 0);

        $emailType = $this->choice("Which type of email would you like to send?", [
            'Both',
            'Welcome Email',
            'Training Email',
        ], 0);

        // default to Active Caregivers
        $users = $business->caregivers()->active()->get();
        $role = substr($userType, strpos($userType, ' ') + 1);
        switch ($userType) {
            case 'Active Clients':
                $users = $business->clients()->active()->get();
                break;
            case 'ALL Clients':
                $users = $business->clients()->get();
                break;
            case 'ALL Caregivers':
                $users = $business->caregivers()->get();
                break;
        }

        $count = $users->count();
        if ($emailType == 'Both') {
            $count = $count * 2;
        }

        if (! $this->confirm("Send {$count} emails to {$business->name}'s {$role}?")) {
            $this->error('Operation canceled.');
            return 1;
        }

        $progressBar = $this->output->createProgressBar($count);

        foreach ($users as $userRole) {
            if ($emailType == 'Both' || $emailType == 'Welcome Email') {
                if ($userRole->user->role_type == 'caregiver') {
                    $userRole->notify(new CaregiverWelcomeEmail($userRole, $business->chain));
                } else {
                    $userRole->notify(new ClientWelcomeEmail($userRole));
                }
                $userRole->update(['welcome_email_sent_at' => Carbon::now()]);
                $progressBar->advance();
                sleep(1);
            }

            if ($emailType == 'Both' || $emailType == 'Training Email') {
                $userRole->notify(new TrainingEmail());
                $userRole->update(['training_email_sent_at' => Carbon::now()]);
                $progressBar->advance();
                sleep(1);
            }
        }

        $progressBar->setProgress($count);
        $this->line('');

        $this->info("Finished sending all {$count} emails.");
        return 0;
    }
}
