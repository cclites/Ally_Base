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
            $table->decimal('sunday', 5, 2)->default(0.0)->after('period');
            $table->decimal('monday', 5, 2)->default(0.0)->after('sunday');
            $table->decimal('tuesday', 5, 2)->default(0.0)->after('monday');
            $table->decimal('wednesday', 5, 2)->default(0.0)->after('tuesday');
            $table->decimal('thursday', 5, 2)->default(0.0)->after('wednesday');
            $table->decimal('friday', 5, 2)->default(0.0)->after('thursday');
            $table->decimal('saturday', 5, 2)->default(0.0)->after('friday');
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
