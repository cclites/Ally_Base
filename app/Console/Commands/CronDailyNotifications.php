<?php

namespace App\Console\Commands;

use App\Notifications\Business\NoProspectContact;
use Illuminate\Console\Command;
use App\Client;
use App\Notifications\Business\ClientBirthday;
use App\CaregiverLicense;
use App\Notifications\Caregiver\CertificationExpiring;
use App\Notifications\Caregiver\CertificationExpired;
use Illuminate\Support\Carbon;
use App\TriggeredReminder;
use App\Prospect;

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

        $this->noProspectContact();

        $this->failedCharge();

        // ======================================
        // CAREGIVER NOTIFICATIONS
        // ======================================
        
        // ======================================
        // MULTI-USER REMINDERS
        // ======================================
        
        $this->expiringCertifications();

        $this->expiredCertifications();
    }

    /**
     * Check for Client birthdays that are today and notify the
     * related Office Users.
     *
     * @return void
     */
    public function clientBirthdays() : void
    {
        $clients = Client::whereHas('user', function ($q) {
            $today = date('m-d');
            $q->where('date_of_birth', 'like', "%-$today")
                ->where('active', 1);
        })->get();

        $triggered = TriggeredReminder::getTriggered(ClientBirthday::getKey(), $clients->pluck('id'));

        foreach ($clients as $client) {
            if ($triggered->contains($client->id)) {
                continue;
            }
 
            \Notification::send($client->business->notifiableUsers(), new ClientBirthday($client));

            TriggeredReminder::markTriggered(ClientBirthday::getKey(), $client->id);
        }
    }

    /**
     * Find prospects that have not had contact for over 14 days.
     *
     * @return void
     */
    public function noProspectContact() : void
    {
        $prospects = Prospect::with('business')
                            ->where('last_contacted', '<=', Carbon::now()->subDays(NoProspectContact::THRESHOLD)->toDateTimeString())
                            ->where('closed_loss', false)
                            ->get();

        $sent = collect([]);
        foreach($prospects as $prospect){
            $users = $prospect->business->notifiableUsers();
            $users = $users->diffAssoc($sent);
            \Notification::send($users, new NoProspectContact($prospect));
            $sent = $sent->merge($users);

            TriggeredReminder::markTriggered(CertificationExpiring::getKey(), $prospect->id);
        }

    }

    /**
     * Find any Caregiver certifications that are expiring soon.
     *
     * @return void
     */
    public function expiringCertifications() : void
    {
        $licenses = CaregiverLicense::with('caregiver')
            ->whereBetween('expires_at', [Carbon::now(), Carbon::now()->addDays(CertificationExpiring::THRESHOLD)])
            ->get();

        $triggered = TriggeredReminder::getTriggered(CertificationExpiring::getKey(), $licenses->pluck('id'));
        foreach ($licenses as $license) {
            if ($triggered->contains($license->id)) {
                continue;
            }

            if (!  $license->caregiver->active) {
                // skip inactive caregivers
                continue;
            }

            // notify the Caregiver that owns the license
            \Notification::send($license->caregiver->user, new CertificationExpiring($license));

            // notify all OfficeUsers that belong to the same businesses as the Caregiver
            $sent = collect([]);
            foreach ($license->caregiver->businesses as $business) {
                $users = $business->notifiableUsers();
                $users = $users->diffAssoc($sent);
                \Notification::send($users, new \App\Notifications\Business\CertificationExpiring($license));
                $sent = $sent->merge($users);
            }

            TriggeredReminder::markTriggered(CertificationExpiring::getKey(), $license->id, $license->expires_at->addDays(1));
        }
    }

    /**
     * Find any Caregiver certifications that have expired.
     *
     * @return void
     */
    public function expiredCertifications() : void
    {
        $licenses = CaregiverLicense::with('caregiver')
            ->whereBetween('expires_at', [Carbon::now()->subDays(30), Carbon::now()])
            ->get();

        $triggered = TriggeredReminder::getTriggered(CertificationExpired::getKey(), $licenses->pluck('id'));

        foreach ($licenses as $license) {
            if ($triggered->contains($license->id)) {
                continue;
            }

            if (!  $license->caregiver->active) {
                // skip inactive caregivers
                continue;
            }

            // notify the Caregiver that owns the license
            \Notification::send($license->caregiver->user, new CertificationExpired($license));

            // notify all OfficeUsers that belong to the same businesses as the Caregiver
            $sent = collect([]);
            foreach ($license->caregiver->businesses as $business) {
                $users = $business->notifiableUsers();
                $users = $users->diffAssoc($sent);
                \Notification::send($users, new \App\Notifications\Business\CertificationExpired($license));
                $sent = $sent->merge($users);
            }

            TriggeredReminder::markTriggered(CertificationExpired::getKey(), $license->id, $license->expires_at->addDays(31));
        }
    }

    public function failedCharge() : void
    {
        //TODO
    }

}
