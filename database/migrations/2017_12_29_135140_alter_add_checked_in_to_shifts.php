<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddCheckedInToShifts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->boolean('checked_in')->after('business_id')->default(0);
        });

        // Update existing records
        if (env('APP_ENV') === 'production') {
            DB::statement('UPDATE shifts SET checked_in = 1 WHERE checked_in_number IS NOT NULL OR checked_in_latitude IS NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('checked_in');
        });
    }
}
