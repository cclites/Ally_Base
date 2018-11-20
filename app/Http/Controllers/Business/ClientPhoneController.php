<?php

namespace App\Http\Controllers\Business;

use App\Client;
use App\Responses\ErrorResponse;

class ClientPhoneController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Client $client
     * @return \Illuminate\Http\Response
     */
    public function index(Client $client)
    {
        $this->authorize('update', $client);

        $numbers = $client->phoneNumbers->sort(function($a, $b) {
            $numberGen = function($type) {
                switch($type) {
                    case 'primary':
                        return 10;
                    default:
                        return 1;
                }
            };
            $aNumber = $numberGen($a->type);
            $bNumber = $numberGen($b->type);
            if ($aNumber > $bNumber) return -1;
            return ($aNumber < $bNumber) ? 1 : 0;
        });
        return response($numbers->values());
    }
}
