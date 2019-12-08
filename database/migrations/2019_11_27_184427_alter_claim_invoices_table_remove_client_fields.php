<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClaimInvoicesTableRemoveClientFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claim_invoices', function (Blueprint $table) {
            $table->dropForeign(['client_invoice_id']);
        });

        Schema::table('claim_invoices', function (Blueprint $table) {
            $table->unsignedInteger('client_id')->nullable()->change();
        });

        Schema::table('claim_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'client_invoice_id',
                'client_first_name',
                'client_last_name',
                'client_dob',
                'client_medicaid_id',
                'client_medicaid_diagnosis_codes',
            ]);
        });

        Schema::table('claimable_services', function (Blueprint $table) {
            $table->dropForeign(['caregiver_id']);
        });

        Schema::table('claimable_services', function (Blueprint $table) {
            $table->dropColumn([
                'caregiver_id',
                'caregiver_first_name',
                'caregiver_last_name',
                'caregiver_gender',
                'caregiver_dob',
                'caregiver_ssn',
                'caregiver_medicaid_id',
            ]);
        });

        Schema::table('claimable_expenses', function (Blueprint $table) {
            $table->dropColumn([
                'caregiver_id',
                'caregiver_first_name',
                'caregiver_last_name',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claim_invoices', function (Blueprint $table) {
            $table->unsignedInteger('client_invoice_id')->nullable();
            $table->unsignedInteger('client_id')->nullable(false)->change();
            $table->string('client_first_name', 45)->nullable();
            $table->string('client_last_name', 45)->nullable();
            $table->date('client_dob')->nullable();
            $table->string('client_medicaid_id', 255)->nullable();
            $table->string('client_medicaid_diagnosis_codes', 255)->nullable();

            // Add foreign keys back also:
            $table->foreign('client_invoice_id')->references('id')->on('client_invoices')->onDelete('RESTRICT')->onUpdate('CASCADE');
        });

        Schema::create('claimable_expenses', function (Blueprint $table) {
            $table->unsignedInteger('caregiver_id')->nullable();
            $table->string('caregiver_first_name', 35)->nullable();
            $table->string('caregiver_last_name', 35)->nullable();
        });

        Schema::create('claimable_services', function (Blueprint $table) {
            $table->unsignedInteger('caregiver_id')->nullable();
            $table->string('caregiver_first_name', 35);
            $table->string('caregiver_last_name', 35);
            $table->char('caregiver_gender', 1)->nullable();
            $table->date('caregiver_dob')->nullable();
            $table->binary('caregiver_ssn')->nullable();
            $table->string('caregiver_medicaid_id', 255)->nullable();

            $table->foreign('caregiver_id')->references('id')->on('caregivers')->onDelete('RESTRICT')->onUpdate('CASCADE');
        });
    }
}
