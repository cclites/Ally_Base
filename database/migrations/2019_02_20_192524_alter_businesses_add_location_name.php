<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBusinessesAddLocationName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('multi_location_registry');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->string('short_name')->after('name')->nullable();
        });

        \App\Business::with('chain')->get()->each(function(\App\Business $business) {
            $chainName = $business->chain->name;
            $shortName = str_replace($chainName, "", $business->name);

            $newName = trim($chainName . ' ' . $shortName);

            $business->short_name = $business->name;
            $business->name =  $newName;
            $business->save();
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->string('short_name')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->tinyInteger('multi_location_registry');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('short_name');
        });
    }
}
