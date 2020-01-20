<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableShiftsAddAdminNoteColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'shifts', function ( Blueprint $table ) {

            $table->string( 'admin_note', 255 )->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'shifts', function ( Blueprint $table ) {

            $table->dropColumn( 'admin_note' );
        });
    }
}
