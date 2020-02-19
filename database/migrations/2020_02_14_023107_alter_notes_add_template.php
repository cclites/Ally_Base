<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterNotesAddTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notes', function(Blueprint $table){
            $table->unsignedInteger('template_id')->nullable()->default(null)->index('template_idx');
            $table->foreign( 'template_id' )->references( 'id' )->on( 'note_templates' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notes', function(Blueprint $table){
            $table->dropColumn('template_id');
        });
    }
}
