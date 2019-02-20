<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToServicesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedule_services', function (Blueprint $table) {
            $table->foreign('schedule_id')->references('id')->on('schedules')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('payer_id')->references('id')->on('payers')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('shift_services', function (Blueprint $table) {
            $table->foreign('shift_id')->references('id')->on('shifts')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign('payer_id')->references('id')->on('payers')->onUpdate('restrict')->onDelete('restrict');
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
            $table->dropForeign(['schedule_id']);
            $table->dropForeign(['payer_id']);
            $table->dropForeign(['service_id']);
        });

        Schema::table('shift_services', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropForeign(['payer_id']);
            $table->dropForeign(['service_id']);
        });
    }
}
