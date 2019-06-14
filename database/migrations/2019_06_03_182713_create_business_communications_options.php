<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBusinessCommunicationsOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();

        Schema::create('business_communications_options', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('auto_off')->default(true);
            $table->boolean('on_indefinitely')->default(false);
            $table->text('week_start');
            $table->text('week_end');
            $table->text('weekend_start');
            $table->text('weekend_end');
            $table->text('message');
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
        Schema::dropIfExists('business_communications_options');
    }
}
