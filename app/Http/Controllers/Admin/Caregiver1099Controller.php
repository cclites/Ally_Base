<?php

namespace App\Http\Controllers\Admin;

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
        $caregiver1099 = new Caregiver1099(
            $request->all()
        );

        $record = collect($caregiver1099->records()[0])->toArray() ;

        $record['year'] = $request->year;
        $record['created_by'] = auth()->user()->nameLastFirst();

        //Store these for the return data object
        $_caregiver_1099 = $record['caregiver_1099'];
        $_business_name = $record['business_name'];

        // These fields are used for filtering the 1099 preview report, but are not
        // actually part of a the caregiver_1099 model
        unset($record['caregiver_1099']);
        unset($record['caregiver_1099_id']);
        unset($record['client_type']);
        unset($record['business_name']);

        $caregiver1099->fill($record)->save();

        \Log::info($caregiver1099->id);

        $caregiver1099->caregiver_1099_id = $caregiver1099->id;
        $caregiver1099->business_name = $_business_name;
        $caregiver1099->caregiver_1099 = $_caregiver_1099;

        \Log::info("RETURN CAREGIVER: ");
        \Log::info($caregiver1099);

        return response()->json($caregiver1099);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
