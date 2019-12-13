<?php

namespace App\Http\Controllers\Admin;

use App\Business;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\ChainClientTypeSettings;
use App\Client;

class ChainSettingsController extends Controller
{
    /**
     * @param ChainClientTypeSettings $chainClientTypeSettings
     * @param Request $request
     * @return SuccessResponse
     */
    public function update(ChainClientTypeSettings $chainClientTypeSettings, Request $request): SuccessResponse
    {
        $input = $request->all();
        $chainClientTypeSettings->fill($input)->save();

        $chainClientTypeSettings->chain->businesses->each(function(Business $business) use($chainClientTypeSettings){

            $business->clients->each(function(Client $client) use($chainClientTypeSettings){

                $this->authorize('update', $client);

                if($client->client_type === 'medicaid' || $client->client_type === 'private_pay'){
                    $client->caregiver_1099 = $chainClientTypeSettings[ $client->client_type . "_1099_from"]; //ally or client
                    $client->lock_1099 = $chainClientTypeSettings[ $client->client_type . "_1099_edit"]; //can edit
                    $client->send_1099 = $chainClientTypeSettings[ $client->client_type . "_1099_default"]; //send by default
                }else{
                    $client->caregiver_1099 = $chainClientTypeSettings["other_1099_from"];
                    $client->lock_1099 = $chainClientTypeSettings["other_1099_edit"];
                    $client->send_1099 = $chainClientTypeSettings[ "other_1099_default"];
                }

                if($client->send_1099 === 'choose'){
                    $client->lock_1099 = 1;
                    unset($client->caregiver_1099);
                }

                $client->save();
            });
        });

        return new SuccessResponse("Settings successfully updated");
    }

}
