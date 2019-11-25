<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Queries\Caregiver1099Query;
use App\Caregiver1099;
use App\Caregiver;
use App\Client;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use App\Http\Requests\storeCaregiver1099Request;
use App\Http\Requests\UpdateCaregiver1099Request;
use App\Http\Requests\Transmit1099Request;
use App\Http\Controllers\Controller;

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
        $recordBucket = [];

        foreach($records as $record)
        {
            $originalRecord = clone $record;
            $record = (array)$record;

            $record['year'] = $request->year;
            $record['created_by'] = auth()->user()->nameLastFirst();
            $record['payment_total'] = floatval($record['payment_total']);

            // These fields are used for filtering the 1099 preview report, but are not
            // actually part of a the caregiver_1099 model
            unset($record['caregiver_1099']);
            unset($record['caregiver_1099_id']);
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
    public function transmit(Transmit1099Request $request): Response
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

        if (empty($rows)) {
            return '';
        }

        \Log::info($rows);

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

            $data = collect($row)
                    ->toArray();

            $csv[] = '"' . implode('","', $data) . '"';
        }

        return implode("\r\n", $csv);
    }
}
