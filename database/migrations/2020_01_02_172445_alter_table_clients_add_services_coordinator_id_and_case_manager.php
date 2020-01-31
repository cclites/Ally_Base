<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableClientsAddServicesCoordinatorIdAndCaseManager extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            if (\DB::getDriverName() != 'sqlite') {
                $table->dropForeign('fk_client_case_manager_id');
            }
            $table->renameColumn('case_manager_id', 'services_coordinator_id');
            $table->foreign('services_coordinator_id', 'fk_services_coordinator_id')->references('id')->on('users');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->string('case_manager')->nullable();
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
            $table->dropForeign('fk_services_coordinator_id');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->renameColumn('services_coordinator_id', 'case_manager_id');
            $table->dropColumn(['case_manager']);
            $table->foreign('case_manager_id', 'fk_client_case_manager_id')->references('id')->on('users');
        });
    }
}
