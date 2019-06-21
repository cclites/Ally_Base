<?php

namespace App\Console\Commands;

use App\Notifications\Caregiver\CertificationExpired;
use App\TriggeredReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use App\CaregiverLicense;

class testEventId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:eventId';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $this->expiredCertifications();
    }

    public function expiredCertifications()
    {
        $licenses = CaregiverLicense::with('caregiver')
            ->whereBetween('expires_at', [Carbon::now()->subDays(30), Carbon::now()])
            ->get();

        //$triggered = TriggeredReminder::getTriggered(CertificationExpired::getKey(), $licenses->pluck('id'));

        foreach ($licenses as $license) {

            // notify all OfficeUsers that belong to the same businesses as the Caregiver
            $sent = collect([]);
            foreach ($license->caregiver->businesses as $business) {
                $users = $business->notifiableUsers();
                $users = $users->diffAssoc($sent);
                \Notification::send($users, new \App\Notifications\Business\CertificationExpired($license));
                $sent = $sent->merge($users);
            }

        }
    }
}
