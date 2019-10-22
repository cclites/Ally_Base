<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAddColumnsToCaregiverApplications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('caregiver_applications', function (Blueprint $table) {
            $table->string('certification', 25)->nullable()->after('position');
            $table->string('license_number', 255)->nullable()->after('certification');
            $table->string('training_school', 255)->nullable()->after('license_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('caregiver_applications', function (Blueprint $table) {
            $table->dropColumn(['certification', 'license_number', 'training_school']);
        });
    }
}
