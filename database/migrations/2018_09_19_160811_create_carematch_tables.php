<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarematchTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_preferences', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->char('gender', 1)->nullable();
            $table->char('language', 2)->nullable();
            $table->string('license')->nullable();
            $table->tinyInteger('minimum_rating')->nullable();

            $table->foreign('id')->references('id')->on('clients')->onDelete('cascade');
        });

        Schema::create('caregiver_availability', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->boolean('monday')->default(1);
            $table->boolean('tuesday')->default(1);
            $table->boolean('wednesday')->default(1);
            $table->boolean('thursday')->default(1);
            $table->boolean('friday')->default(1);
            $table->boolean('saturday')->default(1);
            $table->boolean('sunday')->default(1);
            $table->boolean('morning')->default(1);
            $table->boolean('afternoon')->default(1);
            $table->boolean('evening')->default(1);
            $table->boolean('night')->default(1);
            $table->boolean('live_in')->default(1);
            $table->unsignedInteger('minimum_shift_hours')->default(0);
            $table->unsignedInteger('maximum_shift_hours')->default(100);
            $table->unsignedInteger('maximum_miles')->default(100);

            $table->foreign('id')->references('id')->on('caregivers')->onDelete('cascade');
        });

        Schema::create('caregiver_skills', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('caregiver_id');
            $table->unsignedInteger('activity_id');
            $table->unique(['caregiver_id', 'activity_id']);

            $table->foreign('caregiver_id')->references('id')->on('caregivers')->onDelete('cascade');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
        });

        if (app()->environment() !== 'testing') {
            \App\Caregiver::all()->each(function (\App\Caregiver $caregiver) {
                $caregiver->setAvailability([]); // create default availability for all existing cg's
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_preferences');
        Schema::dropIfExists('caregiver_availability');
        Schema::dropIfExists('caregiver_skills');
    }
}
