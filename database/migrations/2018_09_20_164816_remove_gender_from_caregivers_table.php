<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveGenderFromCaregiversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \App\Caregiver::chunk(100, function ($caregivers) {
            $caregivers->each(function (\App\Caregiver $caregiver) {
                $gender = $caregiver->getOriginal('gender');
                if ($gender && !$caregiver->user->gender) {
                    $caregiver->user->update(['gender' => $gender]);
                }
            });
        });

        Schema::table('caregivers', function (Blueprint $table) {
            $table->dropColumn('gender');
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
