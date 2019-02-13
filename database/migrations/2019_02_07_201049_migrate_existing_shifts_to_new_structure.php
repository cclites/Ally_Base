<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateExistingShiftsToNewStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (app()->environment() === 'testing') {
            return;
        }

        DB::beginTransaction();

        ////////////////////////////////////
        //// Migrate existing shifts
        ////////////////////////////////////

        $count = 0;
        \App\Shift::with(['client', 'client.defaultPayment'])->chunk(1000, function($shifts) {
            $shifts->each(function (\App\Shift $shift) use (&$count) {
                $status = $shift->status === \App\Shift::WAITING_FOR_CHARGE ? \App\Shift::WAITING_FOR_INVOICE : $shift->status;
                $rate = $shift->costs()->getTotalHourlyCost();
                $count += \DB::table('shifts')->where('id', $shift->id)->update([
                    'client_rate' => $rate,
                    'status' => $status,
                ]);
            });
        });


        ////////////////////////////////////
        //// Migrate polymorphic relations
        ////////////////////////////////////

        DB::table('system_exceptions')->where('reference_type', \App\Shift::class)->update(['reference_type' => 'shifts']);
        DB::table('signatures')->where('signable_type', \App\Shift::class)->update(['signable_type' => 'shifts']);


        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
