<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AllowRatesToBeNullableInShifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->decimal('caregiver_rate', 8, 2)->nullable()->change();
            $table->decimal('client_rate', 8, 2)->nullable()->change();
            $table->decimal('provider_fee', 8, 2)->nullable()->change();
        });

        Schema::table('shift_services', function (Blueprint $table) {
            $table->decimal('caregiver_rate', 8, 2)->nullable()->change();
            $table->decimal('client_rate', 8, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->decimal('caregiver_rate', 8, 2)->nullable(false)->change();
            $table->decimal('client_rate', 8, 2)->nullable(false)->change();
            $table->decimal('provider_fee', 8, 2)->nullable(false)->change();
        });

        Schema::table('shift_services', function (Blueprint $table) {
            $table->decimal('caregiver_rate', 8, 2)->nullable(false)->change();
            $table->decimal('client_rate', 8, 2)->nullable(false)->change();
        });
    }
}
