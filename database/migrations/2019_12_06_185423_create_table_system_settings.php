<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSystemSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->string('company_address1');
            $table->string('company_address2')->nullable();
            $table->string('company_city');
            $table->string('company_state');
            $table->string('company_zip');
            $table->string('company_contact_phone');
            $table->string('company_contact_email');
            $table->string('company_contact_name');
            $table->string('company_name');
            $table->string('company_ein');
            $table->timestamps();
        });

        DB::table('system_settings')->insert([
            'company_address1' => '1554 Paoli Pike Suite 251',
            'company_city' => 'West Chester',
            'company_state' => 'PA',
            'company_zip' => '19380',
            'company_name' => 'JTR Solutions LLC',
            'company_contact_name' => 'Tom Rowinski',
            'company_contact_email' => 'support@allyms.com',
            'company_contact_phone' => '(800) 903-0587',
            'company_ein' => '87-0808719',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_settings');
    }
}
