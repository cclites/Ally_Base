<?php

namespace App\Http\Controllers\Admin;

use App\Business\Caregiver1099;
use App\Caregiver;
use App\Client;
use Illuminate\Http\Request;
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



    public function store(Request $request)
    {
        /*$data = Client::find($request->item['client_id'])->load([
                        'addresses',
                        'caregivers',
                        'caregivers.addresses'
                    ])
                    ->whereHas(Caregiver::class, function ($q) use($request){
                       $q->where('id', $request['caregiver_id']);
                    })->get();*/

        $data = Client::where('clients.id', $request->item['client_id'])
            ->whereHas('caregivers', function ($q) use($request){
                $q->where('caregivers.id', $request['caregiver_id']);
            })->get();


        \Log::info(json_encode($data));
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
