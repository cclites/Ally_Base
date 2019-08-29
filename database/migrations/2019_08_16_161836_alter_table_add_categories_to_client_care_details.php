<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAddCategoriesToClientCareDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_care_details', function (Blueprint $table) {
            $table->string('mental_status', 255)->nullable();
            $table->string('prognosis', 255)->nullable();
            $table->text('functional')->nullable();
            $table->string('functional_other', 255)->nullable();
            $table->text('activities_permitted')->nullable();
            $table->string('activities_permitted_other', 255)->nullable();
            $table->string('mobility_other', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_care_details', function (Blueprint $table) {
            $table->dropColumn(['mental_status',
                                'prognosis',
                                'functional',
                                'functional_other',
                                'activities_permitted',
                                'activities_permitted_other',
                                'mobility_other',
                ]);
        });
    }
}