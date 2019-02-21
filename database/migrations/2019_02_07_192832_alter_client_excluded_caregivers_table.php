<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientExcludedCaregiversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_excluded_caregivers', function (Blueprint $table) {
            $table->string('reason', 25)->nullable()->index()->after('caregiver_id');
            $table->date('effective_at')->nullable()->after('note');
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
            $table->dropColumn('reason');
            $table->dropColumn('effective_at');
        });
    }
}
