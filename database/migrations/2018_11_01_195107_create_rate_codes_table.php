<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRateCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rate_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->string('name');
            $table->string('type')->default('caregiver');
            $table->decimal('rate', 5,2)->nullable();
            $table->boolean('fixed')->default(0);
            $table->timestamps();
        });

        Schema::table('caregivers', function (Blueprint $table) {
            $table->unsignedInteger('hourly_rate_id')->nullable();
            $table->unsignedInteger('fixed_rate_id')->nullable();

            $table->foreign('hourly_rate_id')->references('id')->on('rate_codes')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('fixed_rate_id')->references('id')->on('rate_codes')->onUpdate('cascade')->onDelete('restrict');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedInteger('hourly_rate_id')->nullable();
            $table->unsignedInteger('fixed_rate_id')->nullable();

            $table->foreign('hourly_rate_id')->references('id')->on('rate_codes')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('fixed_rate_id')->references('id')->on('rate_codes')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rate_codes');

        Schema::table('caregivers', function (Blueprint $table) {
            $table->dropForeign(['hourly_rate_id']);
            $table->dropForeign(['fixed_rate_id']);
            $table->dropColumn(['hourly_rate_id', 'fixed_rate_id']);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['hourly_rate_id']);
            $table->dropForeign(['fixed_rate_id']);
            $table->dropColumn(['hourly_rate_id', 'fixed_rate_id']);
        });
    }
}
