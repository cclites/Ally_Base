<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMoveMethodToTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gateway_transactions', function (Blueprint $table) {
            $table->string('method_type')->nullable()->after('transaction_type');
            $table->unsignedInteger('method_id')->nullable()->after('method_type');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('method_type');
            $table->dropColumn('method_id');
        });

        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn('method_type');
            $table->dropColumn('method_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gateway_transactions', function (Blueprint $table) {
            $table->dropColumn('method_type');
            $table->dropColumn('method_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('method_type', 45)->nullable();
            $table->string('method_id', 45)->nullable();
        });

        Schema::table('deposits', function (Blueprint $table) {
            $table->string('method_type', 45)->nullable();
            $table->string('method_id', 45)->nullable();
        });
    }
}
