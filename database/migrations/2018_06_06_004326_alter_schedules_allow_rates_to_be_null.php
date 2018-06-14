<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSchedulesAllowRatesToBeNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedules', function(Blueprint $table) {
            $table->decimal('caregiver_rate', 8, 2)->default(null)->nullable()->change();
            $table->decimal('provider_fee', 8, 2)->default(null)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table->decimal('caregiver_rate', 8, 2)->default(0)->change();
        $table->decimal('provider_fee', 8, 2)->default(0)->change();
    }
}
