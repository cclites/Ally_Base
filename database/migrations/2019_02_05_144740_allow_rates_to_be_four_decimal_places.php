<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AllowRatesToBeFourDecimalPlaces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_invoice_items', function (Blueprint $table) {
            $table->decimal('rate', 9, 4)->change();
        });

        Schema::table('caregiver_invoice_items', function (Blueprint $table) {
            $table->decimal('rate', 9, 4)->change();
        });

        Schema::table('business_invoice_items', function (Blueprint $table) {
            $table->decimal('client_rate', 9, 4)->change();
            $table->decimal('caregiver_rate', 9, 4)->change();
            $table->decimal('ally_rate', 9, 4)->change();
            $table->decimal('rate', 9, 4)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_invoice_items', function (Blueprint $table) {
            $table->decimal('rate', 7, 2)->change();
        });

        Schema::table('caregiver_invoice_items', function (Blueprint $table) {
            $table->decimal('rate', 7, 2)->change();
        });

        Schema::table('business_invoice_items', function (Blueprint $table) {
            $table->decimal('client_rate', 7, 2)->change();
            $table->decimal('caregiver_rate', 7, 2)->change();
            $table->decimal('ally_rate', 7, 2)->change();
            $table->decimal('rate', 7, 2)->change();
        });
    }
}
