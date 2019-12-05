<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Queries\Caregiver1099Query;
use App\Caregiver1099;
use App\Caregiver;
use App\Client;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCaregiver1099Request;
use App\Http\Requests\UpdateCaregiver1099Request;
use App\Http\Requests\Transmit1099Request;
use App\Http\Controllers\Controller;
use mikehaertl\pdftk\Pdf;
use mikehaertl\tmp\File;

class Caregiver1099Controller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(StoreCaregiver1099Request $request)
    {
        $query = new Caregiver1099Query; // ->$records;
        $records = $query->_query($request->all());

        foreach($records as $record)
        {
            $record = (array)$record;

            $record['year'] = $request->year;
            $record['created_by'] = auth()->user()->nameLastFirst();
            $record['payment_total'] = floatval($record['payment_total']);

            // These fields are used for filtering the 1099 preview report, but are not
            // actually part of a the caregiver_1099 model
            unset($record['caregiver_1099']);
            unset($record['caregiver_1099_id']);
            unset($record['caregiver_1099_amount']);
            unset($record['caregiver_1099_location']);
            unset($record['client_type']);
            unset($record['business_name']);

            $caregiver1099 = new Caregiver1099($record);
            $caregiver1099->save();
        }

        return new SuccessResponse("Caregiver 1099 has been created");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $caregiver1099 = Caregiver1099::find($id);

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
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCaregiver1099Request $request, $id)
    {
        $caregiver1099 = Caregiver1099::find($id);
        $caregiver1099->fill($request->all());

        $caregiver1099->client_ssn = encrypt($caregiver1099->client_ssn);
        $caregiver1099->caregiver_ssn = encrypt($caregiver1099->caregiver_ssn);

        if($caregiver1099->save()){
            return new SuccessResponse("Caregiver 1099 has been updated");
        }

        return new ErrorResponse("Unable to update Caregiver 1099");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Creates a csv file of 1099s for transmission
     * TODO: Convert to IRS format for direct transmission
     *
     * @param Transmit1099Request $request
     * @return \Illuminate\Http\Response
     */
    public function transmit(Transmit1099Request $request)
    {
        $transmitIds = explode(",", $request->transmitSelected);
        $caregiver1099s = collect();

        foreach($transmitIds as $transmitId=>$value){

            $caregiver1099 = Caregiver1099::find($value);
            $transmitted = $caregiver1099->transmitted_by ? true : false;

            if(! $transmitted){
                $caregiver1099->update(['transmitted_at'=>\Carbon\Carbon::now(),'transmitted_by'=> auth()->user()->id]);
            }else if($transmitted){
                \Log::info("Already transmitted. Do not transmit again");
                continue;
            }

            //decrypt ssns
            $decodedClientSsn = decrypt($caregiver1099->client_ssn);
            $decodedCaregiverSsn = decrypt($caregiver1099->caregiver_ssn);

            // Mask SSNs for admins in UI
            if(env('APP_ENV') === 'testing'){
                $caregiver1099->client_ssn = "***-**-" . substr($decodedClientSsn, -4);
                $caregiver1099->caregiver_ssn = "***-**-" . substr($decodedCaregiverSsn, -4);
            }else{
                $caregiver1099->client_ssn = $decodedClientSsn;
                $caregiver1099->caregiver_ssn = $decodedCaregiverSsn;
            }

            $caregiver1099s->push($caregiver1099);
        }

        $csv = $this->toCsv($caregiver1099s);

        return \Response::make(json_encode($csv), 200, [
            'Content-type' => 'application/csv',
            'Content-Disposition' => 'attachment; filename="Transmission.csv"',
        ]);

    }

    /**
     * @param $data
     * @return string
     */
    private function toCsv($rows){

        if (count($rows) > 1) {
            return '';
        }

        // Build header
        $headerRow = collect($rows[0])
            ->keys()
            ->map(function ($key) {
                return $key === 'id' ? 'ID' : ucwords(str_replace('_', ' ', $key));
            })
            ->toArray();

        // Add header
        $csv[] = '"' . implode('","', $headerRow) . '"';

        // build rows
        foreach ($rows as $row) {



            $data = collect($row)->toArray();

            \Log::info(json_encode($data));

            $csv[] = '"' . implode('","', $data) . '"';
        }

        return implode("\r\n", $csv);
    }

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
