<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuickbooksCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quickbooks_customers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('customer_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->onUpdate('CASCADE')->onDelete('RESTRICT');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedInteger('quickbooks_customer_id')->nullable();
            $table->foreign('quickbooks_customer_id')->references('id')->on('quickbooks_customers')->onUpdate('CASCADE')->onDelete('CASCADE');
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
            $table->dropForeign(['quickbooks_customer_id']);
            $table->dropColumn('quickbooks_customer_id');
        });

        Schema::dropIfExists('quickbooks_customers');
    }
}
