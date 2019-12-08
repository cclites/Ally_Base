<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClaimableServicesTableAddFullInvoiceFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claimable_services', function (Blueprint $table) {
            $table->unsignedInteger('client_signature_id')->nullable()->after('caregiver_comments');
            $table->unsignedInteger('caregiver_signature_id')->nullable()->after('client_signature_id');
            $table->boolean('is_overtime')->default(false)->after('caregiver_signature_id');

            $table->foreign('client_signature_id')->references('id')->on('signatures')->onUpdate('CASCADE')->onDelete('RESTRICT');
            $table->foreign('caregiver_signature_id')->references('id')->on('signatures')->onUpdate('CASCADE')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claimable_services', function (Blueprint $table) {
            $table->dropForeign(['client_signature_id']);
            $table->dropForeign(['caregiver_signature_id']);
        });

        Schema::table('claimable_services', function (Blueprint $table) {
            $table->dropColumn([
                'client_signature_id',
                'caregiver_signature_id',
                'is_overtime',
            ]);
        });
    }
}
