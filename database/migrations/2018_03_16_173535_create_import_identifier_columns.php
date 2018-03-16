<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImportIdentifierColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('import_identifier')->nullable();
        });

        Schema::table('caregivers', function (Blueprint $table) {
            $table->string('import_identifier')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('import_identifier');
        });

        Schema::table('caregivers', function (Blueprint $table) {
            $table->dropColumn('import_identifier')->nullable();
        });
    }
}
