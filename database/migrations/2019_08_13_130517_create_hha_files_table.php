<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHhaFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hha_files', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('claim_id');

            $table->string('filename');
            $table->string('status');

            $table->timestamps();

            $table->foreign('claim_id')->references('id')->on('claims')->onDelete('RESTRICT')->onUpdate('CASCADE');
        });

        Schema::create('hha_file_results', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('hha_file_id');

            $table->dateTime('service_date')->nullable();
            $table->string('reference_id')->nullable();
            $table->string('service_code')->nullable();
            $table->string('status_code');
            $table->string('import_status');

            $table->timestamps();

            $table->foreign('hha_file_id')->references('id')->on('hha_files')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hha_file_results');
        Schema::dropIfExists('hha_files');
    }
}
