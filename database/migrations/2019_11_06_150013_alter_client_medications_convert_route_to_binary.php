<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientMedicationsConvertRouteToBinary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_medications', function (Blueprint $table) {
            $table->renameColumn('route', 'route_old');
        });

        Schema::table('client_medications', function (Blueprint $table) {
            $table->binary('route')->nullable();
            $table->boolean('was_changed')->default(false);
        });

        foreach (\App\ClientMedication::all() as $item) {
            if ($route = $item->getOriginal('route_old')) {
                $item->route = Crypt::decrypt($route);
            }
            $item->was_changed = false;
            if ($newChanged = $item->getOriginal('new_changed')) {
                try {
                    if (Crypt::decrypt($newChanged) == '(C)') {
                        $item->was_changed = true;
                    }
                } catch (\Exception $ex) {
                }
            }
            $item->save();
        }

        Schema::table('client_medications', function (Blueprint $table) {
            $table->dropColumn(['route_old', 'new_changed']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_medications', function (Blueprint $table) {
            $table->dropColumn(['was_changed']);
            $table->binary('new_changed')->nullable();
        });
    }
}
