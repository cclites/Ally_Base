<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Queries\Caregiver1099Query;
use App\Caregiver1099;
use App\Caregiver;
use App\Client;
use Illuminate\Http\Request;
use App\Http\Requests\storeCaregiver1099Request;
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

        foreach($records as $record){

            $record = (array)$record;
            $originalRecord = clone $record;


            $record['year'] = $request->year;
            $record['created_by'] = auth()->user()->nameLastFirst();
            $record['payment_total'] = floatval($record['payment_total']);

            //Store these for the return data object
            $originalRecord['caregiver_1099'] = $record['caregiver_1099'];
            $originalRecord['business_name'] = $record['business_name'];

            // These fields are used for filtering the 1099 preview report, but are not
            // actually part of a the caregiver_1099 model
            unset($record['caregiver_1099']);
            unset($record['caregiver_1099_id']);
            unset($record['client_type']);
            unset($record['business_name']);

            $caregiver1099 = new Caregiver1099($record);

            $caregiver1099->save();
            $recordBucket[] = $caregiver1099->fresh();


        }
        return response()->json($recordBucket);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $caregiver1099 = Caregiver1099::find($id);
        //return view_component('caregiver-1099-edit', 'Edit Caregiver 1099', compact('caregiver1099') );
        return response()->json($caregiver1099);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
}
