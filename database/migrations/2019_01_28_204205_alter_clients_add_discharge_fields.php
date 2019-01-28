<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientsAddDischargeFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->text('discharge_reason')->nullable();
            $table->text('discharge_condition')->nullable();
            $table->text('discharge_goals_eval')->nullable();
            $table->text('discharge_disposition')->nullable();
            $table->text('discharge_internal_notes')->nullable();
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
            $table->dropColumn('discharge_reason');
            $table->dropColumn('discharge_condition');
            $table->dropColumn('discharge_goals_eval');
            $table->dropColumn('discharge_disposition');
            $table->dropColumn('discharge_internal_notes');
        });
    }
}
