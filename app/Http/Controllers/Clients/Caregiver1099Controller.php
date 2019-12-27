<?php

namespace App\Http\Controllers\Clients;

use App\Client;
use App\Caregiver1099;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use mikehaertl\pdftk\Pdf;

class Caregiver1099Controller extends Controller
{

    /**
     * Display a listing of the resource for a single caregiver
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Client $client)
    {
        $caregiver_1099s = $client->caregiver1099s
                            ->map(function($caregiver_1099) use($client){
                                if($caregiver_1099->payer === 'ally'){
                                    return null;
                                }else{
                                    return [
                                        'year'=> $caregiver_1099->year,
                                        'name' => $caregiver_1099->client_first_name . " " . $caregiver_1099->client_last_name,
                                        'id' => $caregiver_1099->id
                                    ];
                                }

                            })
                            ->groupBy('year');

        return response()->json($caregiver_1099s);
    }

    public function downloadPdf(Caregiver1099 $caregiver1099)
    {
        $caregiver1099->load("client");
        $pdf = new Pdf('../resources/pdf_forms/caregiver1099s/' . $caregiver1099->year . '/1099-misc-copy-c.pdf');

        $payerTin = $caregiver1099->client_ssn ? decrypt($caregiver1099->client_ssn) : '';
        $payerName = $clientName = $caregiver1099->client_first_name . " " . $caregiver1099->client_last_name;
        $clAddress2 = $caregiver1099->client_address2 ? $caregiver1099->client_address2 . "\n" : '';
        $caAddress2 = $caregiver1099->caregiver_address2 ? ", " . $caregiver1099->caregiver_address2 : '';
        $payerAddress = $payerName . "\n" . $caregiver1099->client_address1 . "\n" . $clAddress2 . $caregiver1099->client_address3();
        $paymentTotal = $caregiver1099->caregiver_1099_amount ? $caregiver1099->caregiver_1099_amount : $caregiver1099->payment_total;
        $caregiverTin = decrypt($caregiver1099->caregiver_ssn);

        if($caregiver1099->uses_ein_number){
            $caregiverTin = str_replace("-", "", $caregiverTin);
            $caregiverTin = substr($caregiverTin,0, 2) . "-" . substr($caregiverTin, 2,7);
        }

        if($caregiver1099->client->caregiver_1099 === 'ally'){
            $systemSettings = \DB::table('system_settings')->first();

            $payerName = $systemSettings->company_name;
            $payerTin = $systemSettings->company_ein;
            $payerAddress3 = $systemSettings->company_city . ", " . $systemSettings->company_state . " " . $systemSettings->company_zip;
            $clAddress2 = $systemSettings->company_address2 ? $systemSettings->company_address2 . "\n" : '';
            $payerAddress = $payerName . "\n" . $systemSettings->company_address1 . "\n" . $clAddress2 . $payerAddress3;
        }

        $pdf->fillForm([
            /** COPY C **/
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_1[0]' => $payerAddress,
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_2[0]' => $payerTin,
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_3[0]' => "***-**-" . substr($caregiverTin,-4), //recipient tin
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_4[0]' => $caregiver1099->caregiver_first_name . " " . $caregiver1099->caregiver_last_name, //recipient name
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_5[0]' => $caregiver1099->caregiver_address1 . $caAddress2, //recipient street address
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_6[0]' => $caregiver1099->caregiver_address3(), //recipient city, state, zip
            'topmostSubform[0].CopyC[0].RightColumn[0].f2_14[0]' => $paymentTotal,

        ])->execute();

        $fileName = $clientName . '_' . $caregiver1099->caregiver_first_name . "_" . $caregiver1099->caregiver_last_name . '1099.pdf';
        $pdf->send($fileName);
    }
}
