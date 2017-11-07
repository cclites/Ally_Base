<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCaregiverApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('caregiver_applications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('caregiver_application_status_id')->default(1);
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_initial')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->binary('ssn', 65535)->nullable();
            $table->string('address')->nullable();
            $table->string('address_2')->nullable();
            $table->string('city', 60)->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip', 10)->nullable();
            $table->string('cell_phone', 20);
            $table->string('cell_phone_provider', 60)->nullable();
            $table->string('home_phone', 20)->nullable();
            $table->string('email', 100);
            $table->string('emergency_contact_name', 100)->nullable();
            $table->string('emergency_contact_phone', 100)->nullable();
            $table->boolean('worked_here_before')->nullable();
            $table->string('worked_before_location')->nullable();
            $table->unsignedInteger('caregiver_position_id')->nullable();
            $table->date('preferred_start_date')->nullable();
            $table->string('preferred_days', 100)->nullable();
            $table->string('preferred_times')->nullable();
            $table->string('preferred_shift_length')->nullable();
            $table->boolean('work_weekends')->nullable();
            $table->integer('travel_radius')->nullable();
            // driving history
            $table->string('vehicle')->nullable();
            $table->boolean('dui')->nullable();
            $table->boolean('reckless_driving')->nullable();
            $table->boolean('moving_violation')->nullable();
            $table->integer('moving_violation_count')->nullable();
            $table->boolean('accidents')->nullable();
            $table->integer('accident_count')->nullable();
            $table->text('driving_violations_desc')->nullable();
            // criminal history
            $table->boolean('felony_conviction')->nullable();
            $table->boolean('theft_conviction')->nullable();
            $table->boolean('drug_conviction')->nullable();
            $table->boolean('violence_conviction')->nullable();
            $table->text('criminal_history_desc')->nullable();
            // injury status
            $table->boolean('currently_injured')->nullable();
            $table->boolean('previously_injured')->nullable();
            $table->boolean('lift_25_lbs')->nullable();
            $table->boolean('workmans_comp')->nullable();
            $table->string('workmans_comp_dates')->nullable();
            $table->text('injury_status_desc')->nullable();
            // employment history
            for ($i = 1; $i < 4; $i++) {
                $table->string("employer_{$i}_name", 200)->nullable();
                $table->string("employer_{$i}_city", 60)->nullable();
                $table->string("employer_{$i}_state", 2)->nullable();
                $table->date("employer_{$i}_approx_start_date")->nullable();
                $table->date("employer_{$i}_approx_end_date")->nullable();
                $table->string("employer_{$i}_phone", 10)->nullable();
                $table->string("employer_{$i}_job_title", 150)->nullable();
                $table->string("employer_{$i}_supervisor_name", 100)->nullable();
                $table->text("employer_{$i}_reason_for_leaving")->nullable();
            }

            for ($i = 1; $i < 4; $i++) {
                $table->string("reference_{$i}_name", 100)->nullable();
                $table->string("reference_{$i}_phone", 10)->nullable();
                $table->string("reference_{$i}_relationship")->nullable();
            }

            $table->string('heard_about')->nullable();

            $table->boolean('acknowledged_terms')->default(false);

            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('caregiver_applications');
    }
}
