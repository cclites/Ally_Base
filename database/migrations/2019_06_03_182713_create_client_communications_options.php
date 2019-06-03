<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientCommunicationsOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_communications_options', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('auto_off')->default(true);
            $table->boolean('on_indefinitely')->default(false);
            $table->timestamp('week_start')->nullable();
            $table->timestamp('week_end')->nullable();
            $table->timestamp('weekend_start')->nullable();
            $table->timestamp('weekend_end')->nullable();
            $table->text('message');
            $table->unsignedInteger('client_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_communications_options');
    }
}
