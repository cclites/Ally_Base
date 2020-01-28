<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAdminNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_admin_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger( 'creator_user_id' );
            $table->unsignedInteger( 'subject_user_id' );
            $table->text( 'body' );
            $table->timestamps();

            $table->foreign( 'creator_user_id' )->references( 'id' )->on( 'users' )->onDelete( 'CASCADE' );
            $table->foreign( 'subject_user_id' )->references( 'id' )->on( 'users' )->onDelete( 'CASCADE' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_admin_notes');
    }
}
