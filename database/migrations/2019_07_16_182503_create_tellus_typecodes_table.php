<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTellusTypecodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tellus_typecodes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('category', 255)->index();
            $table->string('subcategory', 255)->nullable();
            $table->integer('code')->index();
            $table->string('text_code', 255);
            $table->string('description', 255);
            $table->timestamps();
        });

        Schema::create('tellus_enumerations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('category', 255)->index();
            $table->integer('code')->index();
            $table->string('value', 255);
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
        Schema::dropIfExists('tellus_enumerations');
        Schema::dropIfExists('tellus_typecodes');
    }
}
