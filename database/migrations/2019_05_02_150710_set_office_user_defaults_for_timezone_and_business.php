<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetOfficeUserDefaultsForTimezoneAndBusiness extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (\App\OfficeUser::all() as $user) {
            $defaultBusiness = $user->businesses->first();

            if (empty($defaultBusiness)) {
                continue;
            }

            $user->update([
                'default_business_id' => $defaultBusiness->id,
                'timezone' => $defaultBusiness->timezone,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \App\OfficeUser::whereRaw(1)->update([
            'default_business_id' => null,
            'timezone' => 'America/New_York',
        ]);
    }
}
