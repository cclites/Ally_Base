<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Billing\ClientInvoice;
use App\Billing\Deposit;
use App\Billing\Payment;

class PrepareRequiredDataForPayersAndInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!in_array(app()->environment(), ['production', 'staging'])) return;

        DB::beginTransaction();

        echo("Part 1\n");

        ////////////////////////////////////
        //// Run preparation for each business chain
        ////////////////////////////////////

        $providerPayers = [];

        /** @var \App\BusinessChain $chain */
        foreach(\App\BusinessChain::all() as $chain) {

            $businessIds = $chain->businesses()->pluck('id')->toArray();

            ////////////////////////////////////
            //// Create a default service
            ////////////////////////////////////

            $service = \App\Billing\Service::create([
                'name' => 'Caregiver Service',
                'code' => '',
                'default' => true,
                'chain_id' => $chain->id,
            ]);

            ////////////////////////////////////
            //// Update shifts to use that service
            ////////////////////////////////////

            \App\Shift::whereIn('business_id', $businessIds)->update(['service_id' => $service->id]);

            ////////////////////////////////////
            //// Update schedules to use that service
            ////////////////////////////////////

            \App\Schedule::whereIn('business_id', $businessIds)->update(['service_id' => $service->id]);

            /////////////////////////////////////
            //// Create a new provider payer
            ////////////////////////////////////

            $payer = \App\Billing\Payer::create([
                'name' => $chain->name,
                'week_start' => 1,
                'address1' => $chain->address1,
                'address2' => $chain->address2,
                'city' => $chain->city,
                'state' => $chain->state,
                'zip' => $chain->zip,
                'phone_number' => $chain->phone1,
                'chain_id' => $chain->id,
                'payment_method_type' => 'businesses', // Important
            ]);

            // Update all previous provider payer payments with the payment method
            foreach($businessIds as $businessId) {
                $providerPayers[$businessId] = $payer;
                Payment::whereNull('client_id')->where('business_id', $businessId)->update([
                    'payment_method_type' => 'businesses',
                    'payment_method_id' => $businessId,
                ]);
            }

        }

        ////////////////////////////////////
        //// Update all previous private pay payments with their payment method
        ////////////////////////////////////

        echo("Part 2\n");

        DB::statement("
UPDATE payments p
INNER JOIN gateway_transactions t ON p.transaction_id = t.id
SET p.payment_method_type = t.method_type, p.payment_method_id = t.method_id
WHERE p.client_id IS NOT NULL
");

        ////////////////////////////////////
        //// Assign Balance Payers to Clients
        ////////////////////////////////////

        echo("Part 3\n");

        $rows = DB::affectingStatement("
INSERT INTO client_payers (client_id, payer_id, effective_start, effective_end, payment_allocation)
SELECT DISTINCT c.id, p.id, '2018-01-01', '9999-12-31', 'balance' FROM clients c
INNER JOIN businesses b ON b.id = c.business_id
INNER JOIN payers p ON p.chain_id = b.chain_id
WHERE c.default_payment_type = 'businesses'
");
        echo "$rows affected by provider pay setting\n";

        echo("Part 4\n");

        $rows = DB::affectingStatement("
INSERT INTO client_payers (client_id, payer_id, effective_start, effective_end, payment_allocation)
SELECT id, '0', '2018-01-01', '9999-12-31', 'balance' FROM clients WHERE default_payment_type != 'businesses' OR default_payment_type IS NULL
");
        echo "$rows affected by private pay setting\n";

        \DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
