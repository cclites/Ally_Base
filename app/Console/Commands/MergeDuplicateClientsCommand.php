<?php

namespace App\Console\Commands;

use App\Client;
use App\Responses\ErrorResponse;
use Illuminate\Console\Command;

class MergeDuplicateClientsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:merge {client_1 : The old client ID} {client_2 : The client ID to merge data into}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge two clients and consolidate shifts and invoices.';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        //31920 31951
        $old = Client::findOrFail($this->argument('client_1'));
        $client = Client::findOrFail($this->argument('client_2'));

        if ($old->business_id != $client->business_id) {
            $this->error("You cannot merge $old->name with $client->name because they belong to different businesses.");
            return 0;
        }

        if ($old->hasActiveShift()) {
            $this->error("You cannot deactivate client $old->name because they have an active shift clocked in.");
            return 0;
        }

        if ($old->payments()->exists()) {
            $this->error("You cannot merge $old->name because they have made payments with their own payment methods and these need to be moved manually.");
            return 0;
        }

        if (! $this->confirm("Merge client $old->name (#$old->id) into $client->name (#$client->id)?\nThis will copy over any invoices and shift related data and then de-activate the old client.")) {
            $this->info("Cancelled.");
            return 0;
        }

        if (! $this->confirm("ARE YOU SURE?  THIS CANNOT BE UNDONE")) {
            $this->info("Cancelled.");
            return 0;
        }

        \DB::beginTransaction();

        // Change ownership of models
//        \DB::table('care_plans')->where('client_id', $old->id)->update(['client_id' => $client->id]);
//        \DB::table('notes')->where('client_id', $old->id)->update(['client_id' => $client->id]);
//        \DB::table('prospects')->where('client_id', $old->id)->update(['client_id' => $client->id]);
//        \DB::table('skilled_nursing_pocs')->where('client_id', $old->id)->update(['client_id' => $client->id]);
//        \DB::table('trusts')->where('client_id', $old->id)->update(['client_id' => $client->id]);
//        \DB::table('client_caregivers')->where('client_id', $old->id)->update(['client_id' => $client->id]);
//        \DB::table('client_contacts')->where('client_id', $old->id)->update(['client_id' => $client->id]);
//        \DB::table('client_goals')->where('client_id', $old->id)->update(['client_id' => $client->id]);
//        \DB::table('client_medications')->where('client_id', $old->id)->update(['client_id' => $client->id]);
//        \DB::table('client_meta')->where('client_id', $old->id)->update(['client_id' => $client->id]);
//        \DB::table('client_narrative')->where('client_id', $old->id)->update(['client_id' => $client->id]);
//        \DB::table('client_onboardings')->where('client_id', $old->id)->update(['client_id' => $client->id]);
//        \DB::table('client_payers')->where('client_id', $old->id)->update(['client_id' => $client->id]);
//        \DB::table('client_rates')->where('client_id', $old->id)->update(['client_id' => $client->id]);
//        \DB::table('client_care_details')->where('client_id', $old->id)->update(['client_id' => $client->id]);
//        \DB::table('client_ethnicity_preferences')->where('client_id', $old->id)->update(['client_id' => $client->id]);
//        \DB::table('client_excluded_caregivers')->where('client_id', $old->id)->update(['client_id' => $client->id]);
//        \DB::table('client_agreement_status_history')->where('client_id', $old->id)->update(['client_id' => $client->id]);

        $old->clearFutureSchedules();

        \DB::table('timesheets')->where('client_id', $old->id)->update(['client_id' => $client->id]);
        \DB::table('shifts')->where('client_id', $old->id)->update(['client_id' => $client->id]);
        \DB::table('shift_adjustments')->where('client_id', $old->id)->update(['client_id' => $client->id]);
        \DB::table('shift_confirmations')->where('client_id', $old->id)->update(['client_id' => $client->id]);
        \DB::table('client_authorizations')->where('client_id', $old->id)->update(['client_id' => $client->id]);
        \DB::table('client_invoices')->where('client_id', $old->id)->update(['client_id' => $client->id]);
        \DB::table('payment_queue')->where('client_id', $old->id)->update(['client_id' => $client->id]);
        \DB::table('payments')->where('client_id', $old->id)->update(['client_id' => $client->id]);
        \DB::table('claim_invoices')->where('client_id', $old->id)->update(['client_id' => $client->id]);

        // Move any schedules that match moved shifts
        $scheduleIds = \DB::table('shifts')
            ->where('client_id', $client->id)
            ->whereNotNull('schedule_id')
            ->select('schedule_id')
            ->get()
            ->pluck('schedule_id');

        \DB::table('schedules')
            ->whereIn('id', $scheduleIds)
            ->update(['client_id' => $client->id]);

        // Deactivate the old client
        $old->update([
            'status_alias_id' => null,
            'active' => false,
            'discharge_internal_notes' => "Client was a duplicate of $client->id",
        ]);

        \DB::commit();

        $this->info("Client $old->name (#$old->id) has been merged into $client->name (#$client->id).");

        return 0;
    }
}
