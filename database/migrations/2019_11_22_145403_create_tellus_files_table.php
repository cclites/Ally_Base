<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTellusFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tellus_files', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('claim_id');

            $table->string('filename');
            $table->string('status');

            $table->timestamps();

            $table->foreign('claim_id')->references('id')->on('claims')->onDelete('RESTRICT')->onUpdate('CASCADE');
        });

        Schema::create('tellus_file_results', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tellus_file_id');

            $table->dateTime('service_date')->nullable();
            $table->string('reference_id')->nullable();
            $table->string('service_code')->nullable();
            $table->string('status_code');
            $table->string('import_status');

            $table->timestamps();

            $table->foreign('tellus_file_id')->references('id')->on('tellus_files')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'tellus_file_results' );
        Schema::dropIfExists( 'tellus_files' );
    }
}
