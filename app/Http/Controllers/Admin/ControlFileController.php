<?php

namespace App\Http\Controllers\Admin;

use App\ControlFile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ControlFileController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * resource controller ended up being a lot of guns for a little target.
     * Very open minded to sticking this function in a non-dedicated controller - of course unless the functionality needs to expand one day
     * 
     * leaving the other functions here because why not - let me know if you'd prefer me to remove them
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view_component( 'admin-control-file', 'Control File' );
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ControlFile  $controlFile
     * @return \Illuminate\Http\Response
     */
    public function show(ControlFile $controlFile)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ControlFile  $controlFile
     * @return \Illuminate\Http\Response
     */
    public function edit(ControlFile $controlFile)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ControlFile  $controlFile
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ControlFile $controlFile)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ControlFile  $controlFile
     * @return \Illuminate\Http\Response
     */
    public function destroy(ControlFile $controlFile)
    {
        //
    }
}
