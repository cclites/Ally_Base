<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeServiceAuthorizationFkRestrict extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_authorizations', function (Blueprint $table) {
            if (\DB::getDriverName() != 'sqlite') {
                $table->dropForeign(['service_id']);
            }
            $table->foreign('service_id')->references('id')->on('services')->onDelete('restrict')->onUpdate('cascade');
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
            $table->dropForeign(['service_id']);
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade')->onUpdate('cascade');
        });
    }
}
