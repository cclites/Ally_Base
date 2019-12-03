<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClaimableTablesAddClientFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claimable_expenses', function (Blueprint $table) {
            $table->unsignedInteger('client_id')->nullable()->after('shift_id');
            $table->string('client_first_name', 45)->nullable()->after('client_id');
            $table->string('client_last_name', 45)->nullable()->after('client_first_name');

            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('CASCADE')->onDelete('RESTRICT');
        });

        Schema::table('claimable_services', function (Blueprint $table) {
            $table->unsignedInteger('client_id')->nullable()->after('shift_id');
            $table->string('client_first_name', 45)->nullable()->after('client_id');
            $table->string('client_last_name', 45)->nullable()->after('client_first_name');
            // Only two fields that should actually be nullable:
            $table->date('client_dob')->nullable()->after('client_last_name');
            $table->string('client_medicaid_id', 255)->nullable()->after('client_dob');
            $table->string('client_medicaid_diagnosis_codes', 255)->nullable()->after('client_medicaid_id');

            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('CASCADE')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claimable_expenses', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn([
                'client_id',
                'client_first_name',
                'client_last_name',
            ]);
        });

        Schema::table('claimable_services', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn([
                'client_id',
                'client_first_name',
                'client_last_name',
                'client_dob',
                'client_medicaid_id',
                'client_medicaid_diagnosis_codes',
            ]);
        });
    }
}
