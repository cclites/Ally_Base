<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCaregiverApplicationPositionAndStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('caregiver_applications', function(Blueprint $table) {
            $table->dropColumn('caregiver_position_id');
            $table->string('position')->nullable();
            $table->dropColumn('caregiver_application_status_id');
            $table->string('status')->default('New');
        });

        Schema::dropIfExists('caregiver_positions');
        Schema::dropIfExists('caregiver_application_statuses');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
