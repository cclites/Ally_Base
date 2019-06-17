<?php

namespace App\Console\Commands;

use App\BusinessChain;
use App\Caregiver;
use App\Notifications\CaregiverWelcomeEmail;
use App\Notifications\TrainingEmail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class EmailUnenrolledCaregivers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:unenrolled_caregivers {chain_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email the training and welcome emails to unenrolled caregivers of a business chain.';

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
        $chain = BusinessChain::findOrFail($this->argument('chain_id'));

        /** @var Caregiver[]|Collection $caregivers */
        $caregivers = $chain->caregivers()
            ->active()
            ->hasEmail()
            ->whereNotSetup()
            ->get();

        $count = $caregivers->count();

        $this->info("Found $count caregivers for {$chain->name}.");

        if ($this->confirm("Do you wish to send the welcome email to all $count caregivers?")) {
            foreach($caregivers as $caregiver) {
                $caregiver->update(['welcome_email_sent_at' => Carbon::now()]);
                $caregiver->notify(new CaregiverWelcomeEmail($caregiver, $chain));
                sleep(1);
            }

            $this->output->writeln("$count welcome emails sent.");
        }

        if ($this->confirm("Do you wish to send the training email to all $count caregivers?")) {
            foreach($caregivers as $caregiver) {
                $caregiver->update(['training_email_sent_at' => Carbon::now()]);
                $caregiver->notify(new TrainingEmail());
                sleep(1);
            }

            $this->output->writeln("$count welcome emails sent.");
        }

        $this->output->writeln("Completed.");
    }
}
