<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientAddServiceFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->date('inquiry_date')->nullable();
            $table->date('service_start_date')->nullable();
            $table->string('referral')->nullable();
            $table->string('diagnosis')->nullable();
            $table->boolean('ambulatory')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('inquiry_date');
            $table->dropColumn('service_start_date');
            $table->dropColumn('referral');
            $table->dropColumn('diagnosis');
            $table->dropColumn('ambulatory');
        });
    }
}
