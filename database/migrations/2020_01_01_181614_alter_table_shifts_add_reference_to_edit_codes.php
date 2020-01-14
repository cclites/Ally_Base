<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableShiftsAddReferenceToEditCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'shifts', function ( Blueprint $table ) {

            $table->unsignedInteger( 'visit_edit_action_id' )->nullable();
            $table->unsignedInteger( 'visit_edit_reason_id' )->nullable();

            $table->foreign( 'visit_edit_action_id' )->references( 'id' )->on( 'visit_edit_actions' )->onDelete( 'RESTRICT' );
            $table->foreign( 'visit_edit_reason_id' )->references( 'id' )->on( 'visit_edit_reasons' )->onDelete( 'RESTRICT' );
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

            $table->dropForeign( 'shifts_visit_edit_action_id_foreign' );
            $table->dropForeign( 'shifts_visit_edit_reason_id_foreign' );

            $table->dropColumn( 'visit_edit_action_id' );
            $table->dropColumn( 'visit_edit_reason_id' );
        });
    }
}
