<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsThreadRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_thread_recipients', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sms_thread_id');
            $table->unsignedInteger('user_id');
            $table->string('number', 25);

            $table->timestamps();

            $table->foreign('sms_thread_id')->references('id')->on('sms_threads')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms_thread_recipients');
    }
}
