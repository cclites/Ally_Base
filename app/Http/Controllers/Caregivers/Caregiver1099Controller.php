<?php

namespace App\Http\Controllers\Caregivers;

use App\Caregiver;
use App\Caregiver1099;
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
            return [
                'year'=> $caregiver_1099->year,
                'name' => $caregiver_1099->client_fname . " " . $caregiver_1099->client_lname,
                'id' => $caregiver_1099->id
            ];
        })
            ->groupBy('year');

        return response()->json($caregiver_1099s);
    }

    public function downloadPdf(Caregiver1099 $caregiver1099)
    {
        $caregiver1099->load("client");

        $systemSettings = \DB::table('system_settings')->first();
        $pdf = new Pdf('../resources/pdf_forms/caregiver1099s/' . $caregiver1099->year . '/B_1_2_1099msc.pdf');

        $payerTin = $caregiver1099->client_ssn ? decrypt($caregiver1099->client_ssn) : '';
        $payerName = $clientName = $caregiver1099->client_fname . " " . $caregiver1099->client_lname;
        $clAddress2 = $caregiver1099->client_address2 ? $caregiver1099->client_address2 . "\n" : '';
        $caAddress2 = $caregiver1099->caregiver_address2 ? ", " . $caregiver1099->caregiver_address2 : '';
        $payerAddress = $payerName . "\n" . $caregiver1099->client_address1 . "\n" . $clAddress2 . $caregiver1099->client_address3();

        if($caregiver1099->client->caregiver_1099 === 'ally'){
            $payerName = $systemSettings->company_name;
            $payerTin = $systemSettings->company_ein;
            $payerAddress3 = $systemSettings->company_city . ", " . $systemSettings->company_state . " " . $systemSettings->company_zip;
            $clAddress2 = $systemSettings->company_address2 ? $systemSettings->company_address2 . "\n" : '';
            $payerAddress = $payerName . "\n" . $systemSettings->company_address1 . "\n" . $clAddress2 . $payerAddress3;
        }

        $pdf->fillForm([
            /** COPY B **/
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_1[0]' => $payerAddress,
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_2[0]' => $payerTin, //payers tin
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_3[0]' => decrypt($caregiver1099->caregiver_ssn), //recipient tin
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_4[0]' => $caregiver1099->caregiver_fname . " " . $caregiver1099->caregiver_lname, //recipient name
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_5[0]' => $caregiver1099->caregiver_address1 . $caAddress2, //recipient street address
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_6[0]' => $caregiver1099->caregiver_address3(), //recipient city, state, zip
            'topmostSubform[0].CopyB[0].RightCol[0].f2_14[0]' => $caregiver1099->payment_total,

            /** COPY 1 **/
            'topmostSubform[0].Copy1[0].LeftColumn[0].f2_1[0]' => $payerAddress,
            'topmostSubform[0].Copy1[0].LeftColumn[0].f2_2[0]' => $payerTin, //payers tin
            'topmostSubform[0].Copy1[0].LeftColumn[0].f2_3[0]' => decrypt($caregiver1099->caregiver_ssn), //recipient tin
            'topmostSubform[0].Copy1[0].LeftColumn[0].f2_4[0]' => $caregiver1099->caregiver_fname . " " . $caregiver1099->caregiver_lname, //recipient name
            'topmostSubform[0].Copy1[0].LeftColumn[0].f2_5[0]' => $caregiver1099->caregiver_address1 . $caAddress2, //recipient street address
            'topmostSubform[0].Copy1[0].LeftColumn[0].f2_6[0]' => $caregiver1099->caregiver_address3(), //recipient city, state, zip
            'topmostSubform[0].Copy1[0].RightCol[0].f2_14[0]' => $caregiver1099->payment_total,

            /** COPY 2 **/
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_1[0]' => $payerAddress,
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_2[0]' => $payerTin, //payers tin
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_3[0]' => decrypt($caregiver1099->caregiver_ssn), //recipient tin
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_4[0]' => $caregiver1099->caregiver_fname . " " . $caregiver1099->caregiver_lname, //recipient name
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_5[0]' => $caregiver1099->caregiver_address1 . $caAddress2, //recipient street address
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_6[0]' => $caregiver1099->caregiver_address3(), //recipient city, state, zip
            'topmostSubform[0].Copy2[0].RightCol[0].f2_14[0]' => $caregiver1099->payment_total,

        ])->execute();

        $fileName = $clientName . '_' . $caregiver1099->caregiver_fname . "_" . $caregiver1099->caregiver_lname . '1099.pdf';
        $pdf->send($fileName);
    }
}
