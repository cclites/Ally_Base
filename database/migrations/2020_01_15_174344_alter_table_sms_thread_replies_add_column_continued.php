<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableSmsThreadRepliesAddColumnContinued extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'sms_thread_replies', function ( Blueprint $table ) {

            $table->unsignedInteger( 'continued_thread_id' )->nullable();

            $table->foreign( 'continued_thread_id' )->references( 'id' )->on( 'sms_threads' )->onDelete( 'CASCADE' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'sms_thread_replies', function ( Blueprint $table ) {

            $table->dropForeign([ 'continued_thread_id' ]);
            $table->dropColumn( 'continued_thread_id' );
        });
    }
}
