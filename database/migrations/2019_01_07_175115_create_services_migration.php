<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicesMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 70);  // Max length of 70 characters
            $table->boolean('default')->default(false); // If true, use as the default service for schedules/shifts
            $table->unsignedInteger('chain_id');
            $table->timestamps();

            $table->foreign('chain_id')->references('id')->on('business_chains')->onDelete('cascade')->onUpdate('cascade');
        });

		Schema::table('payer_rates', function(Blueprint $table)
		{
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade')->onUpdate('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::table('payer_rates', function(Blueprint $table)
		{
            $table->dropForeign(['service_id']);
        });
        
        Schema::dropIfExists('services');
    }
}
