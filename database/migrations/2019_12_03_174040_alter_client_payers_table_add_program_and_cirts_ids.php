<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientPayersTableAddProgramAndCirtsIds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_payers', function (Blueprint $table) {
            $table->string('program_number', 32)->nullable()->after('policy_number');
            $table->string('cirts_number', 32)->nullable()->after('program_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_payers', function (Blueprint $table) {
            $table->dropColumn([
                'program_number',
                'cirts_number',
            ]);
        });
    }
}
