<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterNoteAddProspectReferralSource extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->unsignedInteger('prospect_id')->nullable();
            $table->unsignedInteger('referral_source_id')->nullable();
            $table->string('type', 16)->nullable()->default('other');
            $table->foreign('prospect_id', 'fk_notes_prospect_id')->references('id')->on('prospects');
            $table->foreign('referral_source_id', 'fk_notes_referral_source_id')->references('id')->on('referral_sources');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropForeign('fk_notes_prospect_id');
            $table->dropForeign('fk_notes_referral_source_id');
            $table->dropColumn(['prospect_id']);
            $table->dropColumn(['referral_source_id']);
            $table->dropColumn(['type']);
        });
    }
}
