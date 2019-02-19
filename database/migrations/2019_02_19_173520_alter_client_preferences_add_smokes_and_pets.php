<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientPreferencesAddSmokesAndPets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_preferences', function (Blueprint $table) {
            $table->boolean('smokes')->default(0);
            $table->boolean('pets_dogs')->default(0);
            $table->boolean('pets_cats')->default(0);
            $table->boolean('pets_birds')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_preferences', function (Blueprint $table) {
            $table->dropColumn(['smokes' ,'pets_dogs', 'pets_cats', 'pets_birds']);
        });
    }
}
