<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCaregiverLicensesConvertExpiresAtoDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('caregiver_licenses', function(Blueprint $table) {
            $table->date('expires_at')->default(NULL)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('caregiver_licenses', function(Blueprint $table) {
            $table->date('timestamp')->change();
        });
    }
}
