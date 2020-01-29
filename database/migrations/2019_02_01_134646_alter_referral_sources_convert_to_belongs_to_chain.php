<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\ReferralSource;
use App\Business;

class AlterReferralSourcesConvertToBelongsToChain extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('referral_sources', function (Blueprint $table) {
            $table->unsignedInteger('chain_id')->nullable()->after('id');
            if (\DB::getDriverName() != 'sqlite') {
                $table->dropForeign(['business_id']);
            }
        });

        foreach (ReferralSource::all() as $source) {
            $business = Business::find($source->business_id);
            $source->update(['chain_id' => $business->chain->id]);
        }

        Schema::table('referral_sources', function (Blueprint $table) {
            $table->dropColumn('business_id');
            $table->unsignedInteger('chain_id')->nullable(false)->change();
            $table->string('type', 30)->after('chain_id')->default('client');
            $table->foreign('chain_id')->references('id')->on('business_chains');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('referral_sources', function (Blueprint $table) {
            $table->unsignedInteger('business_id')->nullable()->after('id');
            $table->dropForeign(['chain_id']);
        });

        foreach (ReferralSource::all() as $source) {
            $business = $source->businessChain->businesses->first();
            $source->business_id = $business->id;
            $source->save();
        }

        Schema::table('referral_sources', function (Blueprint $table) {
            $table->dropColumn(['chain_id', 'type']);
            $table->unsignedInteger('business_id')->nullable(false)->change();
            $table->foreign('business_id')->references('id')->on('businesses');
        });
    }
}
