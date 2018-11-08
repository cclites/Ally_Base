<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientMedicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_medications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id');
            $table->binary('type', 65535);
            $table->binary('dose', 65535);
            $table->binary('frequency', 65535);
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
        Schema::dropIfExists('client_medications');
    }
}
