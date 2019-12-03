<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClaimInvoiceTablesAddFullInvoiceFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claimable_services', function (Blueprint $table) {
            $table->unsignedInteger('client_signature')->nullable()->after('caregiver_comments');
            $table->unsignedInteger('caregiver_signature')->nullable()->after('client_signature');
            $table->boolean('is_overtime')->default(false)->after('caregiver_signature');

            $table->string('client_case_manager', 55)->nullable()->after('client_medicaid_diagnosis_codes');
            $table->string('client_program_number', 32)->nullable()->after('client_case_manager');
            $table->string('client_cirts_number', 32)->nullable()->after('client_program_number');
            $table->string('client_ltci_policy_number', 32)->nullable()->after('client_cirts_number');
            $table->string('client_ltci_claim_number', 32)->nullable()->after('client_ltci_policy_number');

            $table->foreign('client_signature')->references('id')->on('signatures')->onUpdate('CASCADE')->onDelete('RESTRICT');
            $table->foreign('caregiver_signature')->references('id')->on('signatures')->onUpdate('CASCADE')->onDelete('RESTRICT');
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
            $table->dropForeign(['client_signature']);
            $table->dropForeign(['caregiver_signature']);
        });

        Schema::table('claimable_services', function (Blueprint $table) {
            $table->dropColumn([
                'client_signature',
                'caregiver_signature',
                'client_case_manager',
                'client_program_number',
                'client_cirts_number',
                'client_ltci_policy_number',
                'client_ltci_claim_number',
                'is_overtime',
            ]);
        });
    }
}
