<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftConfirmationTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shift_confirmations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id');
            $table->char('token', 64)->index();

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::create('shift_confirmation_shifts', function (Blueprint $table) {
            $table->unsignedInteger('shift_confirmation_id');
            $table->unsignedInteger('shift_id');

            $table->primary(['shift_confirmation_id', 'shift_id']);
            $table->foreign('shift_confirmation_id')->references('id')->on('shift_confirmations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shift_confirmations');
        Schema::dropIfExists('shift_confirmation_shifts');
    }
}
