<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Mail\ClientShiftSummaryEmail;
use Illuminate\Database\Eloquent\Collection;
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

    /**
     * A list of errors occurred while processing.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Total number of clients that should receive summary emails.
     *
     * @var int
     */
    protected $totalClients = 0;

    /**
     * Total number of clients that were sent summary emails.
     *
     * @var int
     */
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
        foreach ($this->getIncludedBusinesses() as $business) {
            try {
                $this->processEmailsForBusiness($business);
            }
            catch (\Exception $ex) {
                //  failed on business
                app('sentry')->captureException($ex);
                $this->errors[] = "Failed to process emails for business #{$business->id}";
            }
        }

        $this->dispatchResultsEmail(config('ally.cron_results_to'));
    }

    /**
     * Process and send summary emails to matching
     * client's from the specified business.
     *
     * @param \App\Business $business
     */
    protected function processEmailsForBusiness(Business $business) : void
    {
        $clients = $this->getIncludedClientIds($business->id);
        if (empty($clients)) {
            return;
        }

        $report = new UnconfirmedShiftsReport();
        $results = $report->between(Carbon::parse('2017-01-01'), $this->cutOffDateTime($business->timezone))
            ->includeConfirmed()
            ->forBusinesses($business->id)
            ->forClients($clients)
            ->maskNames()
            ->rows()
            ->groupBy('client_id');

        $this->totalClients += $results->count();

        foreach ($results as $client_id => $shifts) {
            try {
                if ($this->sendShiftSummary($shifts)) {
                    $this->totalSent++;
                } else {
                    // invalid email
                    $this->errors[] = "Client #$client_id does not have valid email";
                }
            }
            catch (\Exception $ex) {
                // failed on client
                app('sentry')->captureException($ex);
                $this->errors[] = "Failed to process email for client #$client_id";
            }

            sleep(1);
        }
    }

    /**
     * Send ClientShiftSummaryEmail using the specified list of shifts.
     *
     * @param iterable $shifts
     * @return bool
     */
    protected function sendShiftSummary(iterable $shifts) : bool
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
     * Send email with CRON results.
     *
     * @param string|null $email
     */
    protected function dispatchResultsEmail(?string $email) : void
    {
        if (empty($email)) {
            return;
        }

        $message = "Results from Shift Summary Emails CRON\r\n\r\nTotal Clients: {$this->totalClients}\r\nTotal Sent: {$this->totalSent}\r\nErrors: " . count($this->errors) . "\r\n\r\nError Details:\r\n" . join("\r\n", $this->errors);

        \Mail::raw($message, function($message) use ($email) {
           $message->subject('Ally CRON Results - Shift Summary Emails')
               ->to($email);
        });
    }

    /**
     * Get the businesses that are set up to send shift confirmation emails.
     * Returns an empty array if report is not for email.
     *
     * @return Collection
     */
    protected function getIncludedBusinesses()
    {
        return Business::where('shift_confirmation_email', true)
            ->get();
    }

    /**
     * Filter the clients to only those that have their 
     * weekly summary emails turned ON.
     *
     * @param int $businessId
     * @return array
     */
    protected function getIncludedClientIds($businessId)
    {
        return Client::where('business_id', $businessId)
                ->where('receive_summary_email', 1)
                ->get()
                ->pluck('id')
                ->toArray();
    }

    /**
     * Get the cut off time for when a shift is excluded from this email (Sunday at 11:59:59 in EST)
     *
     * @param string $timezone
     * @return \Carbon\Carbon
     */
    protected function cutOffDateTime(string $timezone)
    {
        return Carbon::now($timezone)->startOfWeek()->subSecond();
    }
}