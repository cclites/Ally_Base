<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterQuickbooksConnectionsAddIsDesktopColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quickbooks_connections', function (Blueprint $table) {
            $table->boolean('is_desktop')->default(false)->after('access_token');
            $table->string('desktop_api_key', 32)->nullable()->unique()->after('is_desktop');
            $table->timestamp('last_connected_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quickbooks_connections', function (Blueprint $table) {
            $table->dropColumn(['is_desktop', 'desktop_api_key', 'last_connected_at']);
        });
    }
}
