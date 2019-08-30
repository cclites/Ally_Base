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
            $table->text('certification_start')->nullable();
            $table->text('certification_end')->nullable();
            $table->integer('medical_record_number')->nullable();
            $table->string('provider_number')->nullable();
            $table->string('principal_diagnosis_icd_cm')->nullable();
            $table->text('principal_diagnosis')->nullable();
            $table->text('principal_diagnosis_date')->nullable();
            $table->string('surgical_procedure_icd_cm')->nullable();
            $table->text('surgical_procedure')->nullable();
            $table->text('surgical_procedure_date')->nullable();

            $table->string('other_diagnosis_icd_cm')->nullable();
            $table->text('other_diagnosis')->nullable();
            $table->text('other_diagnosis_date')->nullable();

            $table->string('other_diagnosis_icd_cm1')->nullable();
            $table->text('other_diagnosis1')->nullable();
            $table->text('other_diagnosis_date1')->nullable();

            $table->string('other_diagnosis_icd_cm2')->nullable();
            $table->text('other_diagnosis2')->nullable();
            $table->text('other_diagnosis_date2')->nullable();

            $table->string('physician_name')->nullable();
            $table->string('physician_address')->nullable();

            $table->string('physician_phone')->nullable();
            $table->text('orders')->nullable();
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
