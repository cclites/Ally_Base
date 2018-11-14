<?php

namespace App\Http\Controllers;

use App\DeactivationReason;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;

class DeactivationReasonController extends Controller
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
        $data = $request->validate([
            'business_id' => 'int|required',
            'type' => 'string|required',
            'name' => 'string|required'
        ]);

        $reason = DeactivationReason::create($data);
        return new SuccessResponse(ucfirst($data['type']) . ' Deactivation Reason created.', $reason);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DeactivationReason  $deactivationReason
     * @return \Illuminate\Http\Response
     */
    public function show(DeactivationReason $deactivationReason)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DeactivationReason  $deactivationReason
     * @return \Illuminate\Http\Response
     */
    public function edit(DeactivationReason $deactivationReason)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DeactivationReason  $deactivationReason
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DeactivationReason $deactivationReason)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DeactivationReason  $deactivationReason
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeactivationReason $deactivationReason)
    {
        //
    }
}
