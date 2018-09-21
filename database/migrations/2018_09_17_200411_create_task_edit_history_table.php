<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskEditHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_edit_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('task_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('office_users');
            $table->foreign('task_id')->references('id')->on('tasks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_edit_history');
    }
}
