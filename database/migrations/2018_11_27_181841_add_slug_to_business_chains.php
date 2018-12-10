<?php

use App\BusinessChain;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSlugToBusinessChains extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_chains', function (Blueprint $table) {
            $table->string('slug')->nullable();
        });

        BusinessChain::all()->each(function (BusinessChain $chain) {
            $chain->update(['slug' => BusinessChain::generateSlug($chain->name)]);
        });

        Schema::table('business_chains', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_chains', function (Blueprint $table) {
            $table->dropColumn(['slug']);
        });
    }
}
