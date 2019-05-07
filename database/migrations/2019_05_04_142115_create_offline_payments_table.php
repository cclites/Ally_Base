<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfflinePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('claim_payments');
        Schema::create('claim_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('claim_id');
            $table->date('payment_date');
            $table->decimal('amount', 9, 2);
            $table->string('type')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('claims', function (Blueprint $table) {
            $table->decimal('amount_paid', 9, 2)->after('amount')->default(0);
            $table->dropColumn('balance');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->decimal('balance', 9, 2)->after('amount')->default(0);
            $table->dropColumn('amount_paid');
        });
    }
}
