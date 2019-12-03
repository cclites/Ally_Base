<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClaimableTablesAddNullRestrictionToClientFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claimable_expenses', function (Blueprint $table) {
            $table->unsignedInteger('client_id')->nullable(false)->change();
            $table->string('client_first_name', 45)->nullable(false)->change();
            $table->string('client_last_name', 45)->nullable(false)->change();
        });

        Schema::table('claimable_services', function (Blueprint $table) {
            $table->unsignedInteger('client_id')->nullable(false)->change();
            $table->string('client_first_name', 45)->nullable(false)->change();
            $table->string('client_last_name', 45)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claimable_expenses', function (Blueprint $table) {
            $table->unsignedInteger('client_id')->nullable(true)->change();
            $table->string('client_first_name', 45)->nullable(true)->change();
            $table->string('client_last_name', 45)->nullable(true)->change();
        });

        Schema::table('claimable_services', function (Blueprint $table) {
            $table->unsignedInteger('client_id')->nullable(true)->change();
            $table->string('client_first_name', 45)->nullable(true)->change();
            $table->string('client_last_name', 45)->nullable(true)->change();
        });
    }
}
