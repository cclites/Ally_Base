<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientContactTableAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'client_contacts', function (Blueprint $table) {

            $table->boolean( 'is_payer' )->default( false )->after( 'name' );
            $table->boolean( 'has_login_access' )->default( false )->after( 'emergency_priority' );
            $table->boolean( 'has_poa' )->default( false )->after( 'relationship_custom' );

            $table->string( 'work_phone', 20 )->nullable();
            $table->string( 'fax_number', 20 )->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'client_contacts', function (Blueprint $table) {

            $this->dropColumn([

                'is_payer',
                'has_poa',
                'has_login_access',
                'work_phone',
                'fax_number',
            ]);
        });
    }
}
