<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientAuthorizationsAddDaysOfTheWeekColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_authorizations', function (Blueprint $table) {
            $table->decimal('sunday', 5, 2)->nullable()->after('period');
            $table->decimal('monday', 5, 2)->nullable()->after('sunday');
            $table->decimal('tuesday', 5, 2)->nullable()->after('monday');
            $table->decimal('wednesday', 5, 2)->nullable()->after('tuesday');
            $table->decimal('thursday', 5, 2)->nullable()->after('wednesday');
            $table->decimal('friday', 5, 2)->nullable()->after('thursday');
            $table->decimal('saturday', 5, 2)->nullable()->after('friday');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_authorizations', function (Blueprint $table) {
            $table->dropColumn(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']);
        });
    }
}
