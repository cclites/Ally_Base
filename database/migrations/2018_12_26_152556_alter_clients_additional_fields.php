<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientsAdditionalFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('hic', 50)->nullable();
            $table->text('travel_directions', 65535)->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->string('disaster_code_plan', 50)->nullable();
            $table->text('disaster_planning', 65535)->nullable();
            $table->boolean('caregiver_1099')->nullable()->default(0);

            $table->foreign('created_by', 'fk_client_created_by')->references('id')->on('users');
            $table->foreign('updated_by', 'fk_client_updated_by')->references('id')->on('users');
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
            $table->dropForeign('fk_client_created_by');
            $table->dropForeign('fk_client_updated_by');

            $table->dropColumn('hic');
            $table->dropColumn('travel_directions');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
            $table->dropColumn('disaster_code_plan');
            $table->dropColumn('disaster_planning');
            $table->dropColumn('caregiver_1099');
        });
    }
}
