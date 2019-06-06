<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConstraintsToCustomFieldTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_custom_fields', function (Blueprint $table) {
            $table->string('user_type', 25)->change();
            $table->string('type', 25)->change();
        });

        Schema::table('business_custom_fields', function (Blueprint $table) {
            $table->unique(['chain_id', 'user_type', 'key']);
        });

        Schema::create('business_custom_field_options', function (Blueprint $table) {
            $table->string('value'. 255)->change();
            $table->string('label', 255)->change();
        });

        Schema::create('business_custom_field_options', function (Blueprint $table) {
            $table->unique(['field_id', 'value']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_custom_fields', function (Blueprint $table) {
            $table->dropUnique(['chain_id', 'user_type', 'key']);
        });

        Schema::table('business_custom_fields', function (Blueprint $table) {
            $table->text('user_type')->change();
            $table->text('type')->change();
        });

        Schema::create('business_custom_field_options', function (Blueprint $table) {
            $table->dropUnique(['field_id\', \'value']);
        });

        Schema::create('business_custom_field_options', function (Blueprint $table) {
            $table->text('value')->change();
            $table->text('label')->change();
        });
    }
}
