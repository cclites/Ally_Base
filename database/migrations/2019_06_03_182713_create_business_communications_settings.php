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
        $this->down();

        Schema::create('business_communications_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('auto_off')->default(true);
            $table->boolean('on_indefinitely')->default(false);
            $table->time('week_start');
            $table->time('week_end');
            $table->time('weekend_start');
            $table->time('weekend_end');
            $table->string('message');
            $table->unsignedInteger('business_id');
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
