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
        // TODO: add this back after testing complete
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
    }
}
