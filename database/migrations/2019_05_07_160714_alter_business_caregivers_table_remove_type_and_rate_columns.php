<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBusinessCaregiversTableRemoveTypeAndRateColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_caregivers', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('default_rate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_caregivers', function (Blueprint $table) {
            $table->string('type', 45)->nullable()->default('Contractor');
            $table->decimal('default_rate')->default(0);
        });
    }
}
