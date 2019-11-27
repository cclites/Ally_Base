<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableOfficeUsersAddReportsToggleBoolean extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'office_users', function ( Blueprint $table ) {

            $table->boolean( 'views_reports' )->default( 1 );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'office_users', function ( Blueprint $table ) {

            $table->dropColumn( 'views_reports' );
        });
    }
}
