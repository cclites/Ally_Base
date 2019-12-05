<?php

namespace App\Http\Controllers\Business;

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

    /**
     * Generate PDF on the fly and download it.
     *
     * @param $id
     */
    public function downloadPdf($id)
    {
        $pdf = new Pdf('../resources/pdfs/2019/CopyB_1099msc.pdf');
        $caregiver1099 = Caregiver1099::find($id);

        $decodedClientSsn = decrypt($caregiver1099->client_ssn);
        $decodedCaregiverSsn = decrypt($caregiver1099->caregiver_ssn);
        $clientName = $caregiver1099->client_fname . " " . $caregiver1099->client_lname;
        $payerAddress = $clientName . "\n" . $caregiver1099->client_address1 . "\n" . $caregiver1099->client_address2 . "\n" . $caregiver1099->client_address3;

        $pdf->fillForm([
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_1[0]' => $payerAddress,
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_2[0]' => $decodedClientSsn, //payers tin
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_3[0]' => $decodedCaregiverSsn, //recipients tin
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_4[0]' => $caregiver1099->caregiver_fname . " " . $caregiver1099->caregiver_lname, //recipient name
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_5[0]' => $caregiver1099->caregiver_address1 . "\n" . $caregiver1099->caregiver_address2, //recipient street address
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_6[0]' => $caregiver1099->caregiver_address3, //recipient city, state, zip
            'topmostSubform[0].CopyB[0].RightCol[0].f2_14[0]' => $caregiver1099->payment_total,
        ])->execute();

        $fileName = $clientName . '_' . $caregiver1099->caregiver_fname . "_" . $caregiver1099->caregiver_lname . '1099.pdf';
        $pdf->send($fileName);
    }
}
