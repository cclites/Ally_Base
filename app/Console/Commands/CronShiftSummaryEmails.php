<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\ClientShiftSummaryEmail;
use App\Shift;

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
    public function handle()
    {
        $unconfirmedShifts = Shift::with(['client', 'caregiver', 'business'])
            ->whereUnconfirmed()
            ->orderBy('checked_in_time', 'asc')
            ->get();

        $shiftsByClient = [];

        foreach ($unconfirmedShifts as $shift) {
            if (! $shift->business->shift_confirmation_email) {
                // if business doesn't choose to send these emails, skip
                continue;
            }

            if ($shift->status == Shift::CLOCKED_IN && ! $shift->business->sce_shifts_in_progress) {
                // if business does not choose to include in progress shifts, skip
                continue;
            }

            if (array_key_exists($shift->client_id, $shiftsByClient)) {
                array_push($shiftsByClient[$shift->client_id], $shift);
            } else {
                $shiftsByClient[$shift->client_id] = [$shift];
            }
        }

        $runningTotal = 0.0;
        foreach ($shiftsByClient as $client_id => $shifts) {
            $client = null;
            $business = null;
            $report = [];

            foreach ($shifts as $s) {
                $client = $s->client;
                $business = $s->business;

                $total = floatval($s->hours) * floatval($s->caregiver_rate);
                $runningTotal += $total;

                array_push($report, [
                    'id' => $s->id,
                    'date' => $s->checked_in_time->format('m/d/Y'),
                    'caregiver' => $s->caregiver->user->maskedName,
                    'hours' => $s->hours,
                    'rate' => number_format($s->caregiver_rate, 2),
                    'total' => number_format($total, 2),
                ]);
            }
            
            $runningTotal = number_format($runningTotal, 2);
            $this->dispatchEmail($client, $report, $runningTotal, $business);

            // break; // <----------------- for testing
        }
    }

    public function dispatchEmail($client, $shifts, $total, $business)
    {
        \Mail::to($client->email)
            ->send(new ClientShiftSummaryEmail($client, $shifts, $total, $business));
    }
}
