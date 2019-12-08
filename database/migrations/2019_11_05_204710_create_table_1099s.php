<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTable1099s extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('caregiver_1099s', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('client_id')->unsigned()->nullable();
            $table->string('client_fname', 45)->nullable();
            $table->string('client_lname', 45)->nullable();
            $table->binary('client_ssn', 65535)->nullable();
            $table->string('client_address1')->nullable();
            $table->string('client_address2')->nullable();
            $table->string('client_city')->nullable();
            $table->string('client_state')->nullable();
            $table->string('client_zip')->nullable();
            $table->integer('caregiver_id')->unsigned()->nullable();
            $table->string('caregiver_fname', 45)->nullable();
            $table->string('caregiver_lname', 45)->nullable();
            $table->binary('caregiver_ssn', 65535)->nullable();
            $table->string('caregiver_address1')->nullable();
            $table->string('caregiver_address2')->nullable();
            $table->string('caregiver_city')->nullable();
            $table->string('caregiver_state')->nullable();
            $table->string('caregiver_zip')->nullable();
            $table->integer('business_id')->unsigned()->nullable();
            $table->decimal('payment_total', 9);
            $table->smallInteger('year');
            $table->string('created_by');
            $table->integer('modified_by')->unsigned()->nullable();
            $table->dateTime('transmitted_at')->nullable();
            $table->integer('transmitted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->foreign('client_id', 'fk_client_id')->references('id')->on('clients')->onDelete('set null');
            $table->foreign('caregiver_id', 'fk_caregiver_id')->references('id')->on('caregivers')->onDelete('set null');
            $table->foreign('business_id', 'fk_business_id')->references('id')->on('businesses')->onDelete('set null');
            $table->unique(['client_id', 'caregiver_id', 'year', 'payment_total'], 'unique_1099_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('caregiver_1099s');
    }
}
