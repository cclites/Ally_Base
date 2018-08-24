<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shift_questions', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('shift_id');
            $table->unsignedInteger('question_id');
            $table->text('answer');

            $table->timestamps();

            $table->foreign('question_id')->references('id')->on('questions');
            $table->foreign('shift_id')->references('id')->on('shifts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shift_questions');
    }
}
