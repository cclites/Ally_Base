<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientAuthorizationsAddServiceAuthIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_authorizations', function (Blueprint $table) {
            $table->string('service_auth_id')->nullable()->after('service_id');
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
            $table->dropColumn('service_auth_id');
        });
    }
}
