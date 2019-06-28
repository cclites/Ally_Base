<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBusinessCommunicationsSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_communications_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reply_option');
            $table->time('week_start');
            $table->time('week_end');
            $table->time('weekend_start');
            $table->time('weekend_end');
            $table->string('message')->nullable();
            $table->unsignedInteger('business_id');

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_communications_settings');
    }
}
