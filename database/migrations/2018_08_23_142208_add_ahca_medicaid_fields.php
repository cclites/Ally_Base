<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAhcaMedicaidFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->binary('ein', 65535)->nullable();
            $table->string('medicaid_id')->nullable();
            $table->string('medicaid_npi_number')->nullable();
            $table->string('medicaid_npi_taxonomy')->nullable();
        });

        Schema::table('caregivers', function (Blueprint $table) {
            $table->string('medicaid_id')->nullable();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->string('medicaid_id')->nullable();
            $table->string('medicaid_diagnosis_codes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('ein');
            $table->dropColumn('medicaid_id');
            $table->dropColumn('medicaid_npi_number');
            $table->dropColumn('medicaid_npi_taxonomy');
        });

        Schema::table('caregivers', function (Blueprint $table) {
            $table->dropColumn('medicaid_id');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('medicaid_id');
            $table->dropColumn('medicaid_diagnosis_codes');
        });
    }
}
