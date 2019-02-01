<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientMedicationsAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_medications', function (Blueprint $table) {
            $table->binary('name', 65535);
            $table->binary('description', 65535);
            $table->binary('side_effects', 65535)->nullable();
            $table->binary('notes', 65535)->nullable();
            $table->binary('tracking', 65535)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_medications', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('description');
            $table->dropColumn('side_effects');
            $table->dropColumn('notes');
            $table->dropColumn('tracking');
        });
    }
}
