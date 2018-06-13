<?php

namespace App\Http\Controllers\Business;

use App\Client;
use App\Responses\ErrorResponse;

class ClientAddressController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Client $client
     * @return \Illuminate\Http\Response
     */
    public function index(Client $client)
    {
        $numbers = $client->addresses->sort(function($a, $b) {
            $numberGen = function($type) {
                switch($type) {
                    case 'evv':
                        return 10;
                    case 'billing':
                        return 9;
                    default:
                        return 1;
                }
            };
            $aNumber = $numberGen($a->type);
            $bNumber = $numberGen($b->type);
            if ($aNumber > $bNumber) return -1;
            return ($aNumber < $bNumber) ? 1 : 0;
        });
        return response($numbers);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Client $client
     * @param  string $type
     * @return \Illuminate\Http\Response
     */
    public function show(Client $client, $type)
    {
        if (!$this->businessHasClient($client)) {
            return new ErrorResponse(403, 'You do not have access to this client.');
        }

        return response($client->addresses()->where('type', $type)->first());
    }

}
