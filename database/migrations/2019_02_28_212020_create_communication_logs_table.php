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
//            $table->unsignedInteger('business_id');
            $table->unsignedInteger('user_id');
            $table->string('channel')->index();
            $table->text('message');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

//            $table->foreign('business_id')->references('id')->on('businesses')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
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
