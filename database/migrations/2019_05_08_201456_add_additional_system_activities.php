<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdditionalSystemActivities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \App\Activity::create(['code' => '028', 'name' => 'Wound Care']);
        \App\Activity::create(['code' => '029', 'name' => 'Respite Care (Skilled Nursing)']);
        \App\Activity::create(['code' => '030', 'name' => 'Respite Care (General)']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function down()
    {
        \App\Activity::whereIn('code', ['028', '029', '030'])->delete();
    }
}
