<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableNotesAddImportReference extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'notes', function (Blueprint $table) {

            $table->unsignedInteger( 'import_id' )->nullable()->after( 'business_id' );

            $table->string( 'tags', 255 )->nullable()->change();

            $table->foreign( 'import_id' )->references( 'id' )->on( 'imports' )->onDelete( 'CASCADE' );
        });

        Schema::table( 'imports', function (Blueprint $table) {

            $table->string( 'type' )->nullable()->after( 'name' );
        });

        \DB::table( 'imports' )->update([ 'type' => 'shift' ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'notes', function (Blueprint $table) {

            $table->string( 'tags', 32 )->nullable()->change();

            $table->dropForeign(['import_id']);
            $table->dropColumn(['import_id']);
        });

        Schema::table( 'imports', function (Blueprint $table) {

            $table->dropColumn(['type']);
        });
    }
}
