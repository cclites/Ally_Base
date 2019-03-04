<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommunicationLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('channel')->index();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('to')->nullable();
            $table->string('from')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('communication_logs');
    }
}
