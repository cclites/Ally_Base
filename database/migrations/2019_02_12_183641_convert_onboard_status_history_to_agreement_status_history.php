<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Client;

class ConvertOnboardStatusHistoryToAgreementStatusHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_agreement_status_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id');
            $table->string('status', 25)->index();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('CASCADE')->onDelete('CASCADE');
        });

        $validClientIds = Client::select('id')->get()->pluck('id');

        foreach (\DB::table('onboard_status_history')->get() as $history) {
            $data = (array) $history;

            if (! $validClientIds->contains($history->client_id)) {
                continue;
            }

            switch ($history->status) {
                case 'emailed_reconfirmation':
                case 'needs_agreement':
                    $data['status'] = Client::NEEDS_AGREEMENT;
                break;
                case 'agreement_signed':
                    $data['status'] = Client::SIGNED_PAPER;
                    break;
                case 'agreement_checkbox':
                case 'reconfirmed_checkbox':
                    $data['status'] = Client::SIGNED_ELECTRONICALLY;
                    break;
                default:
                    break;
            }

            \DB::table('client_agreement_status_history')->insert($data);
        }

        Schema::dropIfExists('onboard_status_history');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('onboard_status_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('client_id');
            $table->string('status');
            $table->timestamps();
        });

        foreach (\DB::table('client_agreement_status_history')->get() as $history) {
            $data = (array) $history;

            switch ($history->status) {
                case Client::NEEDS_AGREEMENT:
                    $data['status'] = 'needs_agreement';
                    break;
                case Client::SIGNED_PAPER:
                    $data['status'] = 'agreement_signed';
                    break;
                case Client::SIGNED_ELECTRONICALLY:
                    $data['status'] = 'reconfirmed_checkbox';
                    break;
                default:
                    break;
            }

            \DB::table('onboard_status_history')->insert($data);
        }

        Schema::dropIfExists('client_agreement_status_history');
    }
}
