<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKnowledgeAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('knowledge_attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('knowledge_id');
            $table->unsignedInteger('attachment_id');

            $table->timestamps();

            $table->foreign('knowledge_id')->references('id')->on('knowledge')->onDelete('cascade');
            $table->foreign('attachment_id')->references('id')->on('attachments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('knowledge_attachments', function (Blueprint $table) {
            $table->dropForeign(['knowledge_id']);
            $table->dropForeign(['attachment_id']);
        });

        Schema::dropIfExists('knowledge_attachments');
    }
}
