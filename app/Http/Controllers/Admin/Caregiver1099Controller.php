<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Queries\Caregiver1099Query;
use App\Caregiver1099;
use App\Caregiver;
use App\Client;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Rules\ValidSSN;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCaregiver1099Request;
use App\Http\Requests\UpdateCaregiver1099Request;
use App\Http\Requests\Transmit1099Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use mikehaertl\pdftk\Pdf;
use mikehaertl\tmp\File;

class Caregiver1099Controller extends Controller
{

    protected $headerRow = [
        'Void (Enter 0 or 1)',
        'Corrected (Enter 0 or 1)',
        'Payer Name',
        'Payer Address',
        'Payer City',
        'Payer State',
        'Payer Zip',
        'Payer Phone',
        'Payer TIN',
        'Recipient TIN',
        'Recipient Name',
        'Recipient Address',
        'Recipient City',
        'Recipient State',
        'Recipient Zip',
        'Acct No',
        '2nd TIN Notice',
        'Box 15A',
        'Box 15B',
        'Box 1 Rents',
        'Box 2 Royalties',
        'Box 3 Other Income',
        'Box 4 Tax Witheld',
        'Box 5',
        'Box 6',
        'Box 7',
        'Box 8',
        'Box 9',
        'Box 10',
        'Box 13',
        'Box 14',
        'Box 16_1',
        'Box 16_2',
        'Box 17_1',
        'Box 17_2',
        'Box 18_1',
        'Box 18_2'
    ];

    /**
     * Display a listing of the resource for a single caregiver
     *
     * @return Response
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
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */

    public function store(StoreCaregiver1099Request $request)
    {
        $query = new Caregiver1099Query();
        $records = $query->generateReport($request->validated());
        
        foreach($records as $record)
        {
            $record = (array)$record;
            $data = [
                'year'=>$request->year,
                'created_by'=>auth()->user()->nameLastFirst(),
                'payment_total'=>floatval($record['payment_total']),
                'client_id'=>$record['client_id'],
                'client_fname'=>$record['client_fname'],
                'client_lname'=>$record['client_lname'],
                'client_address1'=>$record['client_address1'],
                'client_address2'=>$record['client_address2'],
                'client_city'=>$record['client_city'],
                'client_state'=>$record['client_state'],
                'client_zip'=>$record['client_zip'],
                'client_ssn'=>$record['client_ssn'],
                'caregiver_id'=>$record['caregiver_id'],
                'caregiver_fname'=>$record['caregiver_fname'],
                'caregiver_lname'=>$record['caregiver_lname'],
                'caregiver_address1'=>$record['caregiver_address1'],
                'caregiver_address2'=>$record['caregiver_address2'],
                'caregiver_city'=>$record['caregiver_city'],
                'caregiver_state'=>$record['caregiver_state'],
                'caregiver_zip'=>$record['caregiver_zip'],
                'caregiver_ssn'=>$record['caregiver_ssn'],
            ];

            $caregiver1099 = new Caregiver1099($data);
            $caregiver1099->save();
        }


        return new SuccessResponse("Caregiver 1099 has been created");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Caregiver1099 $caregiver1099
     * @return Response
     */
    public function edit(Caregiver1099 $caregiver1099)
    {
        //decode the ssns
        $decodedClientSsn = decrypt($caregiver1099->client_ssn);
        $decodedCaregiverSsn = decrypt($caregiver1099->caregiver_ssn);

        $caregiver1099->client_ssn = "***-**-" . substr($decodedClientSsn, -4);
        $caregiver1099->caregiver_ssn = "***-**-" . substr($decodedCaregiverSsn, -4);

        return response()->json($caregiver1099);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(UpdateCaregiver1099Request $request, Caregiver1099 $caregiver1099)
    {
        $caregiver1099->fill($request->validated());

        if( strpos($caregiver1099->client_ssn, "#") !== false ){
            $this->validate($request, [
                'client_ssn' => ['required', new ValidSSN()],
            ]);
            $caregiver1099->client_ssn = encrypt($caregiver1099->client_ssn);
        }else{
            unset($caregiver1099->client_ssn);
        }

        if( strpos($caregiver1099->caregiver_ssn, "#") !== false ){
            $this->validate($request, [
                'caregiver_ssn' => ['required', new ValidSSN()],
            ]);
            $caregiver1099->caregiver_ssn = encrypt($caregiver1099->caregiver_ssn);
        }else{
            unset($caregiver1099->caregiver_ssn);
        }

        if($caregiver1099->save()){
            return new SuccessResponse("Caregiver 1099 has been updated");
        }

        return new ErrorResponse("Unable to update Caregiver 1099");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Creates a csv file of 1099s for transmission
     *
     * @param Transmit1099Request $request
     * @return Response
     */
    public function transmit(Caregiver1099 $caregiver1099, $year)
    {
        $systemSettings = \DB::table('system_settings')->first();

        $caregiver1099s = $caregiver1099
                            ->where('year', $year)
                            ->whereNull('transmitted_at')
                            ->with('client')
                            ->get()
                            ->map(function($cg1099) use($systemSettings){

                                if(Caregiver1099::getErrors()){
                                }

                                //$cg1099->update(['transmitted_at'=>\Carbon\Carbon::now(),'transmitted_by'=> auth()->user()->id]);

                                $payerTin = $cg1099->client_ssn ? decrypt($cg1099->client_ssn) : '';
                                $payerName = $cg1099->client_fname . " " . $cg1099->client_lname;
                                $payerAddress = $cg1099->client_address1 . ($cg1099->client_address2 ? ", " . $cg1099->client_address2 : '');
                                $payerCity = $cg1099->client_city;
                                $payerState = $cg1099->client_state;
                                $payerZip = $cg1099->client_zip;
                                $payerPhone = $cg1099->client_phone;
                                $caregiverTin = decrypt($cg1099->caregiver_ssn);

                                if($cg1099->uses_ein_number){
                                    $caregiverTin = str_replace("-", "", $caregiverTin);
                                    $caregiverTin = substr($caregiverTin,0, 2) . "-" . substr($caregiverTin, 2,7);
                                }

                                if($cg1099->client->caregiver_1099 === 'ally'){
                                    $payerName = $systemSettings->company_name;
                                    $payerTin = $systemSettings->company_ein;
                                    $payerCity = $systemSettings->company_city;
                                    $payerState = $systemSettings->company_state;
                                    $payerZip = $systemSettings->company_zip;
                                    $payerAddress = $systemSettings->company_address1 . ($systemSettings->company_address2 ? ", " . $systemSettings->company_address2 : '');
                                    $payerPhone = $systemSettings->company_contact_phone;
                                }

                                return [
                                    'payer_name'=>$payerName,
                                    'payer_address' => $payerAddress,
                                    'payer_city' => $payerCity,
                                    'payer_state' => $payerState,
                                    'payer_zip' => $payerZip,
                                    'payer_phone' => $payerPhone,
                                    'payer_tin' => $payerTin,
                                    'recipient_tin' => $caregiverTin,
                                    'recipient_name' => $cg1099->caregiver_fname . " " . $cg1099->caregiver_lname,
                                    'recipient_address' => $cg1099->caregiver_address1 . "\n" . filled($cg1099->caregiver_address2),
                                    'recipient_city' => $cg1099->caregiver_city,
                                    'recipient_state' => $cg1099->caregiver_state,
                                    'recipient_zip' => $cg1099->caregiver_zip,
                                    'payment_total' => $cg1099->payment_total,
                                ];
                            });

        $csv = $this->toCsv($caregiver1099s);

        return \Response::make(json_encode($csv), 200, [
            'Content-type' => 'application/csv',
            'Content-Disposition' => 'attachment; filename="Transmission.csv"',
        ]);

    }

    /**
     * Convert 1099 data to CSV
     *
     * @param $data
     * @return string
     */
    private function toCsv($rows){

        if (count($rows) < 1) {
            return '';
        }

        // Add header
        $csv[] = '"' . implode('","', $this->headerRow) . '"';

        // build rows
        foreach ($rows as $row) {

            $data = [
                0,
                0,
                $row['payer_name'],
                $row['payer_address'],
                $row['payer_city'],
                $row['payer_state'],
                $row['payer_zip'],
                $row['payer_phone'],
                $row['payer_tin'],
                $row['recipient_tin'],
                $row['recipient_name'],
                $row['recipient_address'],
                $row['recipient_city'],
                $row['recipient_state'],
                $row['recipient_zip'],
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                $row['payment_total'],
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            ];

            $csv[] = '"' . implode('","', $data) . '"';
        }

        $imploded =  implode("\r\n", $csv);

        //remove comment character from data and return
        return str_replace("#", "", $imploded);
    }

    /**
     * Generate PDF on the fly and download it.
     *
     * @param Caregiver1099 $caregiver1099
     */
    public function downloadPdf(Caregiver1099 $caregiver1099)
    {
        $caregiver1099->load("client");
        $pdf = new Pdf('../resources/pdf_forms/caregiver1099s/' . $caregiver1099->year . '/f1099msc_19.pdf');

        $payerTin = $caregiver1099->client_ssn ? decrypt($caregiver1099->client_ssn) : '';
        $payerName = $clientName = $caregiver1099->client_fname . " " . $caregiver1099->client_lname;
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

            /** COPY A **/
            'topmostSubform[0].CopyA[0].LeftColumn[0].f2_1[0]' => $payerAddress,
            'topmostSubform[0].CopyA[0].LeftColumn[0].f2_2[0]' => $payerTin, //payers tin
            'topmostSubform[0].CopyA[0].LeftColumn[0].f2_3[0]' => "***-**-" . substr($caregiverTin,-4),  //recipient tin
            'topmostSubform[0].CopyA[0].LeftColumn[0].f2_4[0]' => $caregiver1099->caregiver_fname . " " . $caregiver1099->caregiver_lname, //recipient name
            'topmostSubform[0].CopyA[0].LeftColumn[0].f2_5[0]' => $caregiver1099->caregiver_address1 . $caAddress2, //recipient street address
            'topmostSubform[0].CopyA[0].LeftColumn[0].f2_6[0]' => $caregiver1099->caregiver_address3(), //recipient city, state, zip
            'topmostSubform[0].CopyA[0].RightCol[0].f2_14[0]' => $paymentTotal,

            /** COPY B **/
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_1[0]' => $payerAddress,
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_2[0]' => $payerTin, //payers tin
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_3[0]' => "***-**-" . substr($caregiverTin,-4), //recipient tin
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_4[0]' => $caregiver1099->caregiver_fname . " " . $caregiver1099->caregiver_lname, //recipient name
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_5[0]' => $caregiver1099->caregiver_address1 . $caAddress2, //recipient street address
            'topmostSubform[0].CopyB[0].LeftColumn[0].f2_6[0]' => $caregiver1099->caregiver_address3(), //recipient city, state, zip
            'topmostSubform[0].CopyB[0].RightCol[0].f2_14[0]' => $paymentTotal,

            /** COPY 1 **/
            'topmostSubform[0].Copy1[0].LeftColumn[0].f2_1[0]' => $payerAddress,
            'topmostSubform[0].Copy1[0].LeftColumn[0].f2_2[0]' => $payerTin, //payers tin
            'topmostSubform[0].Copy1[0].LeftColumn[0].f2_3[0]' => "***-**-" . substr($caregiverTin,-4), //recipient tin
            'topmostSubform[0].Copy1[0].LeftColumn[0].f2_4[0]' => $caregiver1099->caregiver_fname . " " . $caregiver1099->caregiver_lname, //recipient name
            'topmostSubform[0].Copy1[0].LeftColumn[0].f2_5[0]' => $caregiver1099->caregiver_address1 . $caAddress2, //recipient street address
            'topmostSubform[0].Copy1[0].LeftColumn[0].f2_6[0]' => $caregiver1099->caregiver_address3(), //recipient city, state, zip
            'topmostSubform[0].Copy1[0].RightCol[0].f2_14[0]' => $paymentTotal,

            /** COPY 2 **/
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_1[0]' => $payerAddress,
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_2[0]' => $payerTin, //payers tin
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_3[0]' => "***-**-" . substr($caregiverTin,-4), //recipient tin
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_4[0]' => $caregiver1099->caregiver_fname . " " . $caregiver1099->caregiver_lname, //recipient name
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_5[0]' => $caregiver1099->caregiver_address1 . $caAddress2, //recipient street address
            'topmostSubform[0].Copy2[0].LeftColumn[0].f2_6[0]' => $caregiver1099->caregiver_address3(), //recipient city, state, zip
            'topmostSubform[0].Copy2[0].RightColumn[0].f2_14[0]' => $paymentTotal,

            /** COPY C **/
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_1[0]' => $payerAddress,
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_2[0]' => $payerTin, //payers tin
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_3[0]' => "***-**-" . substr($caregiverTin,-4), //recipient tin
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_4[0]' => $caregiver1099->caregiver_fname . " " . $caregiver1099->caregiver_lname, //recipient name
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_5[0]' => $caregiver1099->caregiver_address1 . $caAddress2, //recipient street address
            'topmostSubform[0].CopyC[0].LeftColumn[0].f2_6[0]' => $caregiver1099->caregiver_address3(), //recipient city, state, zip
            'topmostSubform[0].CopyC[0].RightColumn[0].f2_14[0]' => $paymentTotal,

        ])->execute();

        $fileName = $clientName . '_' . $caregiver1099->caregiver_fname . "_" . $caregiver1099->caregiver_lname . '1099.pdf';
        $pdf->send($fileName);
    }

    public function admin(){

        $years = \DB::table('caregiver_1099s')->distinct()->pluck('year');

        return view_component(
            'caregiver-1099-admin',
            'Admin 1099',
            [
                'years'=>$years
            ],
            [
                'Home' => route('home'),
                '1099' => route('admin.admin-1099-actions')
            ]
        );
    }
}
