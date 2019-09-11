<?php

use App\Signature;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateSignatureMetaField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Signature::where( 'signable_type', 'shifts' )->update([

            'meta_type' => 'client',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Signature::where( 'signable_type', 'shifts' )->update([

            'meta_type' => null,
        ]);
    }
}
