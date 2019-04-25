<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBusinessesAddHhaAndTellusCredentials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('hha_username')->nullable();
            $table->binary('hha_password')->nullable();
            $table->string('tellus_username')->nullable();
            $table->binary('tellus_password')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('hha_username');
            $table->dropColumn('hha_password');
            $table->dropColumn('tellus_username');
            $table->dropColumn('tellus_password');
        });
    }
}
