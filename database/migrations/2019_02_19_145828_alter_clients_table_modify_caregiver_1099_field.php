<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Client;

class AlterClientsTableModifyCaregiver1099Field extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('caregiver_1099')->nullable()->default(NULL)->change();
        });

        Client::where('caregiver_1099', '1')->update([
            'caregiver_1099' => 'ally',
        ]);

        Client::withTrashed()->where('caregiver_1099', '<>', 'ally')->update([
            'caregiver_1099' => null,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Client::withTrashed()->where('caregiver_1099', 'ally')->update([
            'caregiver_1099' => 1,
        ]);
        
        Client::withTrashed()->where('caregiver_1099', 'client')->update([
            'caregiver_1099' => 0,
        ]);
        
        Client::withTrashed()->whereNull('caregiver_1099')->update([
            'caregiver_1099' => 0,
        ]);
        
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('caregiver_1099')->nullable()->default(0)->change();
        });
    }
}
