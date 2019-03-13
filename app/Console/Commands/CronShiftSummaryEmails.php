<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Mail\ClientShiftSummaryEmail;
use Illuminate\Support\Str;
use App\ShiftConfirmation;
use App\Reports\UnconfirmedShiftsReport;
use App\Business;
use App\Client;

class CronShiftSummaryEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:shift_summary_emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send client summary shifts confirmation and pending charge email.';

    protected $errors = [];
    protected $totalClients = 0;
    protected $totalSent = 0;

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
     * @return void
     */
    public function handle()
    {
        foreach ($this->getIncludedBusinessIds() as $businessId) {
            try {
                $this->processEmailsForBusiness($businessId);
            }
            catch (\Exception $ex) {
                //  failed on business
                $this->errors[] = "Failed to process emails for business #$businessId";
            }
        }

    protected function processEmailsForBusiness(int $businessId) : void
    {
        $clients = $this->getIncludedClientIds($businessId);

        if (empty($clients)) {
            return;
        }

        $report = new UnconfirmedShiftsReport();
        $results = $report->between(Carbon::parse('2017-01-01'), $this->cutOffDateTime())
            ->includeConfirmed()
            ->includeClockedIn()
            ->includeInProgress()
            ->forBusinesses($businessId)
            ->forClients($clients)
            ->maskNames()
            ->rows()
            ->groupBy('client_id');

        $this->totalClients += $results->count();

        foreach ($results as $client_id => $shifts) {
            try {
                if ($this->sendToClient($shifts)) {
                    $this->totalSent++;
                } else {
                    // invalid email
                    $this->errors[] = "Client #$client_id does not have valid email";
                }
            }
            catch (\Exception $ex) {
                // failed on client
                $this->errors[] = "Failed to process email for client #$client_id";
            }

//            sleep(1); // sleep 1s after each email
            // break; // <----------------- for testing
        }
    }

    public function sendToClient(iterable $shifts) : bool
    {
        $client = $shifts->first()->client;
        $businessName = $shifts->first()->business_name;
        $total = $shifts->sum('total');

        $confirmation = ShiftConfirmation::create([
            'client_id' => $client->id,
            'token' => Str::random(64),
        ]);
        $confirmation->shifts()->sync($shifts->pluck('id'));

        if ($email = filter_var($client->email, FILTER_VALIDATE_EMAIL)) {
            \Mail::to($client->email)->send(new ClientShiftSummaryEmail(
                $client,
                $shifts,
                $total,
                $businessName,
                $confirmation->token
            ));
            return true;
        }

        return false;
    }

    /**
     * Get the businesses that are set up to send shift confirmation emails.
     * Returns an empty array if report is not for email.
     *
     * @return array
     */
    protected function getIncludedBusinessIds()
    {
        return Business::where('shift_confirmation_email', true)
            ->get()
            ->pluck('id')
            ->toArray();
    }

    /**
     * Filter the clients to only those that have their 
     * weekly summary emails turned ON.
     *
     * @param int $business
     * @return array
     */
    protected function getIncludedClientIds($business)
    {
        return Client::where('business_id', $business)
                ->where('receive_summary_email', 1)
                ->get()
                ->pluck('id')
                ->toArray();
    }

    /**
     * Get the cut off time for when a shift is excluded from this email (Sunday at 11:59:59 in EST)
     *
     * @return \Carbon\Carbon
     */
    protected function cutOffDateTime()
    {
        return Carbon::now('America/New_York')->startOfWeek()->subSecond();
    }
}