<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCaregiversAddSmokingAndPetsPreferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('caregivers', function (Blueprint $table) {
            $table->boolean('smoking_okay')->default(0);
            $table->boolean('pets_dogs_okay')->default(0);
            $table->boolean('pets_cats_okay')->default(0);
            $table->boolean('pets_birds_okay')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('caregivers', function (Blueprint $table) {
            $table->dropColumn(['smoking_okay', 'pets_dogs_okay', 'pets_cats_okay', 'pets_birds_okay']);
        });
    }
}
