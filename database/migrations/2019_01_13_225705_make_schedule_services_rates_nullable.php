<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeScheduleServicesRatesNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedule_services', function (Blueprint $table) {
            $table->decimal('client_rate', 7, 2)->nullable()->change();
            $table->decimal('caregiver_rate', 7, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedule_services', function (Blueprint $table) {
            $table->decimal('client_rate', 7, 2)->nullable(false)->change();
            $table->decimal('caregiver_rate', 7, 2)->nullable(false)->change();
        });
    }
}
