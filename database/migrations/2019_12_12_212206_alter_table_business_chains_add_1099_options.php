<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableBusinessChainsAdd1099Options extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'business_chains', function ( Blueprint $table ) {
            $table->string( 'medicaid_1099_default' )->nullable();
            $table->string( 'medicaid_1099_send' )->nullable();
            $table->string( 'medicaid_1099_from' )->nullable();
            $table->string( 'private_pay_1099_default' )->nullable();
            $table->string( 'private_pay_1099_send' )->nullable();
            $table->string( 'private_pay_1099_from' )->nullable();
            $table->string( 'other_1099_default' )->nullable();
            $table->string( 'other_1099_send' )->nullable();
            $table->string( 'other_1099_from' )->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'business_chains', function ( Blueprint $table ) {
            $table->dropColumn([
                'medicaid_1099_default',
                'medicaid_1099_send',
                'medicaid_1099_from',
                'private_pay_1099_default',
                'private_pay_1099_send',
                'private_pay_1099_from',
                'other_1099_default',
                'other_1099_send',
                'other_1099_from'
            ]);
        });
    }
}
