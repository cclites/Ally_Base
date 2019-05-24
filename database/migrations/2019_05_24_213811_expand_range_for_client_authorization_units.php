<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExpandRangeForClientAuthorizationUnits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_authorizations', function (Blueprint $table) {
            $table->decimal('units', 7, 2)->change();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->string('code', 30 )->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
