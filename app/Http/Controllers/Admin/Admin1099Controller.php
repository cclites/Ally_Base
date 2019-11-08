<?php

namespace App\Http\Controllers\Admin;

use App\Business;
use App\Reports\Admin1099PreviewReport;
use App\Http\Controllers\Business\BaseController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Admin1099Controller extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view_component('admin-ten-ninety-nine', '1099');
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

    /**
     * Generates a list of emails for all registries and returns it in a view
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function RegistryEmailList(){

        $emails = Business::whereNotNull('contact_email')->pluck('contact_email')->toArray();
        $emailString = implode(",", $emails);

        return view_component('admin-registry-emails', 'Registry Emails', ['emails' => $emailString]);
    }

}
