<?php

namespace App\Console\Commands;

use App\Notifications\CaregiverWelcomeEmail;
use App\Traits\Console\HasProgressBars;
use App\Notifications\TrainingEmail;
use Illuminate\Support\Collection;
use Illuminate\Console\Command;
use App\BusinessChain;
use App\ClientType;
use Carbon\Carbon;
use App\Caregiver;

class EmailCaregiversCommand extends Command
{
    use HasProgressBars;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:caregivers {chain_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email the training and/or welcome emails to caregivers of a business chain.  (Options and confirmations will be required before anything is sent using this command.)';

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

        $query = $chain->caregivers()->hasEmail()->active();

        $this->info("Found {$query->count()} Caregivers for chain {$chain->name} that are active and have an email address.");

        $enrollmentStatus = $this->choice('Filter by Caregiver\'s enrollment status?', [
            'Any',
            'Enrolled',
            'Not Enrolled',
        ], 2);

        switch ($enrollmentStatus) {
            case 'Any':
                break;
            case 'Enrolled':
                $query->whereHas('user', function ($q) {
                    $q->where('setup_status', '=', Caregiver::SETUP_ADDED_PAYMENT);
                });
                break;
            case 'Not Enrolled':
                $query->whereHas('user', function ($q) {
                    $q->where('setup_status', '<>', Caregiver::SETUP_ADDED_PAYMENT)
                        ->orWhereNull('setup_status');
                });
                break;
        }

        if ($enrollmentStatus != 'Any') {
            $this->info("Found {$query->count()} Caregivers that are $enrollmentStatus.");
        }

        $clientType = $this->choice('Filter for a specific client type?', [
            'Any',
            ClientType::MEDICAID,
            ClientType::PRIVATE_PAY,
            ClientType::LEAD_AGENCY,
            ClientType::LTCI,
            ClientType::VA,
        ], 0);

        if ($clientType != 'Any') {
            $query->whereHas('clients', function ($q) use ($clientType) {
                $q->where('client_type', $clientType);
            });

            $this->info("Found {$query->count()} Caregivers that have clients of type $clientType.");
        }

        /** @var Caregiver[]|Collection $caregivers */
        $caregivers = $query->get();
        $count = $caregivers->count();

        if ($count === 0) {
            $this->info("No matching Caregivers, cannot continue.");
            return 0;
        }

        if ($this->confirm("Do you wish to send the welcome email to all $count caregivers?")) {
            $this->startProgress('Sending welcome emails...', $count);

            foreach($caregivers as $caregiver) {
                $caregiver->update(['welcome_email_sent_at' => Carbon::now()]);
                $caregiver->notify(new CaregiverWelcomeEmail($caregiver, $chain));
                sleep(1);
                $this->advance();
            }

            $this->finish();

            $this->info("$count welcome emails sent.");
        }

        if ($this->confirm("Do you wish to send the training email to all $count caregivers?")) {
            $this->startProgress('Sending training emails...', $count);

            foreach($caregivers as $caregiver) {
                $caregiver->update(['training_email_sent_at' => Carbon::now()]);
                $caregiver->notify(new TrainingEmail());
                sleep(1);
                $this->advance();
            }

            $this->finish();

            $this->info("$count training emails sent.");
        }

        $this->info("Operation complete.");
    }
}
