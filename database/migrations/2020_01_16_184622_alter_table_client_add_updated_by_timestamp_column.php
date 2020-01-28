<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableClientAddUpdatedByTimestampColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'clients', function( Blueprint $table ){

            $table->dateTime( 'updated_by_timestamp' )->nullable()->after( 'updated_by' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'clients', function( Blueprint $table ){

            $table->dropColumn( 'updated_by_timestamp' );
        });
    }
}
