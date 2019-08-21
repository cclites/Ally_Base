<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAddVehicleToClientMedications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_medications', function (Blueprint $table) {
            $table->string('route', 255)->nullable();
            $table->string('new_changed', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_care_details', function (Blueprint $table) {
            $table->dropColumn(['route',
                'new_changed',
            ]);
        });
    }
}
