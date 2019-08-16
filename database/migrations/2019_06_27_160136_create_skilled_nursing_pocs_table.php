<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSkilledNursingPocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skilled_nursing_pocs', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('client_id')->unique();
            $table->date('certification_start');
            $table->date('certification_end');
            $table->integer('medical_record_id');
            $table->string('provider_number');
            $table->string('principal_diagnosis_icd_cm')->nullable();
            $table->text('principal_diagnosis')->nullable();
            $table->date('principal_diagnosis_date')->nullable();
            $table->string('surgical_diagnosis_icd_cm')->nullable();
            $table->text('surgical_diagnosis')->nullable();
            $table->date('surgical_diagnosis_date')->nullable();
            $table->string('other_diagnosis_icd_cm')->nullable();
            $table->text('other_diagnosis')->nullable();
            $table->date('other_diagnosis_date')->nullable();
            $table->text('safety_measures')->nullable();
            $table->text('nutritional_requirements')->nullable();
            $table->text('orders')->nullable();
            $table->string('physicians_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('skilled_nursing_pocs');
    }
}
