<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterChainsAddEnableScheduleGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_chains', function (Blueprint $table) {
            $table->boolean('enable_schedule_groups')->default(true);
        });

        // All new businesses should use schedule groups, all existing businesses should default to off
        DB::table('business_chains')->update(['enable_schedule_groups' => false]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_chains', function (Blueprint $table) {
            $table->dropColumn(['enable_schedule_groups']);
        });
    }
}
