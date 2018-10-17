<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\ClientShiftSummaryEmail;
use App\Shift;
use Illuminate\Support\Str;
use App\ShiftConfirmationToken;
use App\ShiftConfirmation;
use App\Reports\UnconfirmedShiftsReport;

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
    public function handle(UnconfirmedShiftsReport $report)
    {
        $unconfirmedShifts = $report->includeClockedIn()
            ->forEmail()
            ->rows()
            ->groupBy('client_id');

        foreach ($unconfirmedShifts as $client_id => $shifts) {
            $client = $shifts->first()->client;
            $businessName = $shifts->first()->business_name;
            $total = $shifts->sum('total');

            $confirmation = ShiftConfirmation::create([
                'client_id' => $client->id,
                'token' => Str::random(64),
            ]);
            $confirmation->shifts()->sync($shifts->pluck('id'));

            \Mail::to($client->email)->send(new ClientShiftSummaryEmail(
                $client,
                $shifts,
                $total,
                $businessName,
                $confirmation->token
            ));
            
            // break; // <----------------- for testing
        }
    }
}