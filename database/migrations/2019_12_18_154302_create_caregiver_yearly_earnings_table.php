<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCaregiverYearlyEarningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('caregiver_yearly_earnings', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedSmallInteger('year')->index();
            $table->unsignedInteger('caregiver_id');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('business_id');
            $table->decimal('earnings', 9, 2)->index();

            $table->timestamps();

            $table->unique(['caregiver_id', 'client_id', 'year']);
            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('caregiver_id')->references('id')->on('caregivers')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('business_id')->references('id')->on('businesses')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('caregiver_yearly_earnings');
    }
}
