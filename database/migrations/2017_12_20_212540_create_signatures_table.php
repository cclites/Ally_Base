<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSignaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('signatures', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('signable_id');
            $table->string('signable_type', 80);
            $table->binary('content');
            $table->timestamps();
        });
        
        Schema::table('shifts', function(Blueprint $table) {
            $table->dropColumn('signature');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('signatures');

        Schema::table('shifts', function (Blueprint $table) {
            $table->dateTime('signature')->nullable();
        });
    }
}
