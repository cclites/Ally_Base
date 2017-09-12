<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeCaregiverNullableForPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedInteger('caregiver_id')->nullable()->change();
        });
        Schema::table('payment_queue', function (Blueprint $table) {
            $table->unsignedInteger('caregiver_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedInteger('caregiver_id')->change();
        });
        Schema::table('payment_queue', function (Blueprint $table) {
            $table->unsignedInteger('caregiver_id')->change();
        });
    }
}
