<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClientAndCaregiverFieldsToClaimInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claim_invoice_items', function (Blueprint $table) {
            $table->unsignedInteger('client_id')->nullable()->after('claimable_type'); // cannot be null
            $table->string('client_first_name', 45)->nullable()->after('client_id'); // cannot be null
            $table->string('client_last_name', 45)->nullable()->after('client_first_name'); // cannot be null
            $table->date('client_dob')->nullable()->after('client_last_name');
            $table->string('client_medicaid_id', 255)->nullable()->after('client_dob');
            $table->string('client_medicaid_diagnosis_codes', 255)->nullable()->after('client_medicaid_id');
            $table->string('client_case_manager', 55)->nullable()->after('client_medicaid_diagnosis_codes');
            $table->string('client_program_number', 32)->nullable()->after('client_case_manager');
            $table->string('client_cirts_number', 32)->nullable()->after('client_program_number');
            $table->string('client_ltci_policy_number', 32)->nullable()->after('client_cirts_number');
            $table->string('client_ltci_claim_number', 32)->nullable()->after('client_ltci_policy_number');
            $table->string('client_hic', 255)->nullable()->after('client_ltci_claim_number');
            $table->string('client_invoice_notes', 255)->nullable()->after('client_hic');

            $table->unsignedInteger('caregiver_id')->nullable()->after('client_invoice_notes'); // cannot be null
            $table->string('caregiver_first_name', 45)->nullable()->after('caregiver_id'); // cannot be null
            $table->string('caregiver_last_name', 45)->nullable()->after('caregiver_first_name'); // cannot be null
            $table->char('caregiver_gender', 1)->nullable()->after('caregiver_last_name');
            $table->date('caregiver_dob')->nullable()->after('caregiver_gender');
            $table->binary('caregiver_ssn')->nullable()->after('caregiver_dob');
            $table->string('caregiver_medicaid_id', 255)->nullable()->after('caregiver_ssn');

            // Foreign keys
            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('CASCADE')->onDelete('RESTRICT');
            $table->foreign('caregiver_id')->references('id')->on('caregivers')->onUpdate('CASCADE')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claim_invoice_items', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['caregiver_id']);
        });

        Schema::table('claim_invoice_items', function (Blueprint $table) {
            $table->dropColumn([
                'client_id',
                'client_first_name',
                'client_last_name',
                'client_dob',
                'client_medicaid_id',
                'client_medicaid_diagnosis_codes',
                'client_case_manager',
                'client_program_number',
                'client_cirts_number',
                'client_ltci_policy_number',
                'client_ltci_claim_number',
                'client_hic',
                'client_invoice_notes',

                'caregiver_id',
                'caregiver_first_name',
                'caregiver_last_name',
                'caregiver_gender',
                'caregiver_dob',
                'caregiver_ssn',
                'caregiver_medicaid_id',
            ]);
        });
    }
}
