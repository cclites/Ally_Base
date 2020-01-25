<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertPayerAssignmentsToClientPayerAssignments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_invoices', function (Blueprint $table) {
            if (\DB::getDriverName() != 'sqlite') {
                $table->dropForeign(['payer_id']);
            }
            $table->dropColumn(['payer_id']);
        });

        Schema::table('client_invoices', function (Blueprint $table) {
            $table->unsignedInteger('client_payer_id')->after('client_id')->nullable();
            $table->foreign('client_payer_id')->references('id')->on('client_payers')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_invoices', function (Blueprint $table) {
            $table->dropForeign(['client_payer_id']);
            $table->dropColumn(['client_payer_id']);
        });

        Schema::table('client_invoices', function (Blueprint $table) {
            $table->unsignedInteger('payer_id')->after('client_id')->nullable();
            $table->foreign('payer_id')->references('id')->on('payers')->onDelete('restrict');
        });
    }
}
