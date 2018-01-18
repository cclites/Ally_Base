<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCaregiversAddW9Fields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('caregivers', function (Blueprint $table) {
            $table->string('w9_name')->nullable();
            $table->string('w9_business_name')->nullable();
            $table->string('w9_tax_classification')->nullable();
            $table->string('w9_llc_type')->nullable();
            $table->string('w9_exempt_payee_code')->nullable();
            $table->string('w9_exempt_fatca_reporting_code')->nullable();
            $table->string('w9_address')->nullable();
            $table->string('w9_city_state_zip')->nullable();
            $table->string('w9_account_numbers')->nullable();
            $table->binary('w9_ssn', 65535)->nullable();
            $table->string('w9_employer_id_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('caregivers', function (Blueprint $table) {
            $table->dropColumn('w9_name');
            $table->dropColumn('w9_business_name');
            $table->dropColumn('w9_tax_classification');
            $table->dropColumn('w9_llc_type');
            $table->dropColumn('w9_exempt_payee_code');
            $table->dropColumn('w9_exempt_fatca_reporting_code');
            $table->dropColumn('w9_address');
            $table->dropColumn('w9_city_state_zip');
            $table->dropColumn('w9_account_numbers');
            $table->dropColumn('w9_ssn');
            $table->dropColumn('w9_employer_id_number');
        });
    }
}
