<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToSignaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('signatures', function (Blueprint $table) {
            $table->index('signable_id');
            $table->index('signable_type');
        });

        Schema::table('client_payers', function (Blueprint $table) {
            $table->index('effective_start');
            $table->index('effective_end');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('active');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->index('client_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('signatures', function (Blueprint $table) {
            $table->dropIndex(['signable_id']);
            $table->dropIndex(['signable_type']);
        });
        Schema::table('client_payers', function (Blueprint $table) {
            $table->dropIndex(['effective_start']);
            $table->dropIndex(['effective_end']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['active']);
        });
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['client_type']);
        });
    }
}
