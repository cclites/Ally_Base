<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientExcludedCaregiversTableAddNote extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_excluded_caregivers', function (Blueprint $table) {
            $table->text('note')->after('caregiver_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_excluded_caregivers', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
}
