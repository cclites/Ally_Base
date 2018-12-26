<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNoteTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('note_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('short_name', 32)->nullable();
            $table->boolean('active')->default(0);
            $table->text('note');
            $table->timestamps();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('business_id');
            
            $table->foreign('business_id')->references('id')->on('businesses');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('note_templates', function (Blueprint $table) {
            Schema::dropIfExists('note_templates');
        });
    }
}
