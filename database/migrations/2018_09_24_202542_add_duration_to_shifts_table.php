<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDurationToShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->decimal('hours', 6, 2)->after('caregiver_comments')->nullable();
        });

        // Persist all existing shift hours
//        \DB::beginTransaction();
//        \App\Shift::chunk(100, function($shifts) {
//            $shifts->each(function (\App\Shift $shift) {
//                $shift->update(['hours' => $shift->duration()]);
//            });
//        });
//        \DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('hours');
        });
    }
}
