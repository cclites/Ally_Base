<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCaregiverApplicationsTableAddTechnologyColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('caregiver_applications', function (Blueprint $table) {
            $table->string('cell_phone', 20)->nullable()->change();
            $table->boolean('has_cell_phone')->default(false);
            $table->boolean('has_smart_phone')->default(false);
            $table->boolean('can_text')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('caregiver_applications', function (Blueprint $table) {
            $table->dropColumn([
                'has_cell_phone',
                'has_smart_phone',
                'can_text',
            ]);
            $table->string('cell_phone', 20)->nullable(false)->change();
        });
    }
}
