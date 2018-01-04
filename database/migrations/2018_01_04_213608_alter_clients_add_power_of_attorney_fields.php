<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientsAddPowerOfAttorneyFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('poa_first_name', 50)->nullable();
            $table->string('poa_last_name', 50)->nullable();
            $table->string('poa_phone', 25)->nullable();
            $table->string('poa_relationship', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('poa_first_name');
            $table->dropColumn('poa_last_name');
            $table->dropColumn('poa_phone');
            $table->dropColumn('poa_relationship');
        });
    }
}
