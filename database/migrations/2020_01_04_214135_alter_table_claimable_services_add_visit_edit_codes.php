<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableClaimableServicesAddVisitEditCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'claimable_services', function ( Blueprint $table ) {

            $table->unsignedBigInteger( 'visit_edit_action' )->nullable();
            $table->unsignedBigInteger( 'visit_edit_reason' )->nullable();

            $table->foreign( 'visit_edit_action' )->references( 'code' )->on( 'visit_edit_actions' )->onDelete( 'RESTRICT' );
            $table->foreign( 'visit_edit_reason' )->references( 'code' )->on( 'visit_edit_reasons' )->onDelete( 'RESTRICT' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'claimable_services', function ( Blueprint $table ) {

            $table->dropForeign( 'claimable_services_visit_edit_action_foreign' );
            $table->dropForeign( 'claimable_services_visit_edit_reason_foreign' );

            $table->dropColumn( 'visit_edit_action' );
            $table->dropColumn( 'visit_edit_reason' );
        });
    }
}
