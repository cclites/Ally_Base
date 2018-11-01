<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientOnboardingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_onboardings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id');
            $table->boolean('facility')->nullable();
            $table->text('facility_instructions')->nullable();
            $table->text('primary_conditions')->nullable();
            $table->text('service_reasons')->nullable();
            $table->text('service_goals')->nullable();
            $table->text('allergies')->nullable();
            $table->text('medical_equipment')->nullable();
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->string('physician_name')->nullable();
            $table->string('physician_phone')->nullable();
            $table->string('physician_address')->nullable();
            $table->string('pharmacy_name')->nullable();
            $table->string('pharmacy_phone')->nullable();
            $table->string('pharmacy_address')->nullable();
            $table->boolean('hospice_care')->nullable();
            $table->string('hospice_office_location')->nullable();
            $table->string('hospice_case_manager')->nullable();
            $table->string('hospice_phone')->nullable();
            $table->boolean('dnr')->nullable();
            $table->string('dnr_location')->nullable();
            $table->string('ec_name')->nullable();
            $table->string('ec_address')->nullable();
            $table->string('ec_phone_number')->nullable();
            $table->string('ec_email')->nullable();
            $table->string('ec_relation_ship')->nullable();
            $table->boolean('ec_poa')->nullable();
            $table->string('secondary_ec_name')->nullable();
            $table->string('secondary_ec_address')->nullable();
            $table->string('secondary_ec_phone_number')->nullable();
            $table->string('secondary_ec_email')->nullable();
            $table->string('secondary_ec_relation_ship')->nullable();
            $table->boolean('secondary_ec_poa')->nullable();
            $table->boolean('emp_leave_region')->nullable();
            $table->text('emp_with_who_where')->nullable();
            $table->boolean('emp_remain_home')->nullable();
            $table->boolean('emp_shelter')->nullable();
            $table->string('emp_shelter_type')->nullable();
            $table->string('emp_shelter_address')->nullable();
            $table->boolean('emp_shelter_registration_assistance')->nullable();
            $table->string('emp_evacuation_responsible_party')->nullable();
            $table->boolean('emp_caregiver_required')->nullable();
            $table->string('cg_gender_pref')->nullable();
            $table->string('cg_attire_pref')->nullable();
            $table->boolean('pets')->nullable();
            $table->text('pets_description')->nullable();
            $table->boolean('cg_pet_assistance')->nullable();
            $table->boolean('transportation')->nullable();
            $table->string('transportation_vehicle')->nullable();
            $table->date('requested_start_at')->nullable();
            $table->text('requested_schedule')->nullable();
            $table->string('relation_to_intake_party')->nullable();
            $table->string('signature')->nullable();

            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_onboardings');
    }
}
