<?php

namespace App\Http\Controllers\Admin;

use App\Client;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @param Request $request
     */
    public function index(Request $request)
    {
        $clients = Client::active()
            ->join('users', 'users.id', '=', 'clients.id')
            ->orderBy('users.lastname')
            ->orderBy('users.firstname')
            ->get();

        return response($clients);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(){}

    /**
     * Store a newly created resource in storage.
     * 
     * @param Request $request
     */
    public function store(Request $request){}

    /**
     * Display the specified resource.
     * 
     * @param Client $client
     */
    public function show(Client $client){}

    /**
     * Show the form for editing the specified resource.
     * 
     * @param Client $client
     */
    public function edit(Client $client){}

    /**
     * Update the specified resource in storage.
     * 
     * @param Request $request
     * @param Client $client
     */
    public function update(Request $request, Client $client){}

    /**
     * Remove the specified resource from storage.
     * 
     * @param Client $business
     */
    public function destroy(Client $client){}
}
