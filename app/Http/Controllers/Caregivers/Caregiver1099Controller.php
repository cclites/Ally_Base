<?php

namespace App\Http\Controllers\Caregivers;

use App\Caregiver;
use App\Caregiver1099;
use App\Caregiver1099Payer;
use App\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use mikehaertl\pdftk\Pdf;

class Caregiver1099Controller extends Controller
{

    /**
     * Display a listing of the resource for a single caregiver
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Caregiver $caregiver)
    {
        $caregiver_1099s = $caregiver->caregiver1099s->map(function($caregiver_1099){

            if($caregiver_1099->payer == Caregiver1099Payer::ALLY()){
                $client = Client::find($caregiver_1099->client_id);
                $name = $client->first_name . " " . $client->last_name;
            }else{
                $name = $caregiver_1099->client_first_name . ' ' . $caregiver_1099->client_last_name;
            }

            return [
                'year'=> $caregiver_1099->year,
                'name' => $name,
                'id' => $caregiver_1099->id
            ];
        })
            ->groupBy('year');

        return response()->json($caregiver_1099s);
    }

    public function downloadPdf(Caregiver1099 $caregiver1099)
    {
        // TODO: Unmask payer and caregiver SSN
        $pdf = $caregiver1099->getFilledCaregiverPdf(false, true);
        $fileName = $caregiver1099->client->last_name . '_' . $caregiver1099->caregiver_first_name . "_" . $caregiver1099->caregiver_last_name . '1099.pdf';
        $pdf->send($fileName);
    }
}
