<?php

use App\Business;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddBusinessIdToBankAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->unsignedInteger('business_id')->nullable()->after('user_id');
        });

        Business::all()->each(function (Business $business) {
            if ($business->bankAccount) {
                $business->bankAccount->update(['business_id' => $business->id]);
            }
            if ($business->paymentAccount) {
                $business->paymentAccount->update(['business_id' => $business->id]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn('business_id');
        });
    }
}
