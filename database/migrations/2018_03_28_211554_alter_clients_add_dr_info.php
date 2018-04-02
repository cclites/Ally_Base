<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientsAddDrInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('dr_first_name', 50)->nullable();
            $table->string('dr_last_name', 50)->nullable();
            $table->string('dr_phone', 25)->nullable();
            $table->string('dr_fax', 25)->nullable();
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
            $table->dropColumn('dr_first_name');
            $table->dropColumn('dr_last_name');
            $table->dropColumn('dr_phone');
            $table->dropColumn('dr_fax');
        });
    }
}
