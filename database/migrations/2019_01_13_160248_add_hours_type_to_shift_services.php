<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHoursTypeToShiftServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shift_services', function (Blueprint $table) {
            $table->string('hours_type')->default('default')->after('payer_id');
        });

        Schema::table('schedule_services', function (Blueprint $table) {
            $table->string('hours_type')->default('default')->after('payer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shift_services', function (Blueprint $table) {
            $table->dropColumn('hours_type');
        });
        Schema::table('schedule_services', function (Blueprint $table) {
            $table->dropColumn('hours_type');
        });
    }
}
