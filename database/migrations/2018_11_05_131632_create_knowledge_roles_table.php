<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKnowledgeRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('knowledge_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('knowledge_id');
            $table->string('role', 25)->index();

            $table->foreign('knowledge_id')->references('id')->on('knowledge')->onDelete('cascade');
            $table->unique(['knowledge_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('knowledge_roles', function (Blueprint $table) {
            $table->dropForeign(['knowledge_id']);
        });

        Schema::dropIfExists('knowledge_roles');
    }
}
