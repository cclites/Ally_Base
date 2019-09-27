<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNotesToClientAndDepositInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'client_invoices', function (Blueprint $table) {

            $table->string( 'notes', 255 );
        });

        Schema::table( 'caregiver_invoices', function (Blueprint $table) {

            $table->string( 'notes', 255 );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'client_invoices', function (Blueprint $table) {

            $table->dropColumn(['notes']);
        });

        Schema::table( 'caregiver_invoices', function (Blueprint $table) {

            $table->dropColumn(['notes']);
        });
    }
}
