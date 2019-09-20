<?php

namespace App\Http\Controllers\Clients;

use App\Caregiver;
use App\Http\Controllers\Controller;

class CaregiverController extends Controller
{
    /**
     * List all caregivers for the client
     */
    public function index()
    {
        $client = auth()->user()->role;
        $caregivers = $client->caregivers()
            ->with('address', 'phoneNumber')
            ->orderByName()
            ->get();

        return view('clients.caregiver_list', compact('caregivers'));
    }

    /**
     * @param Caregiver $caregiver
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Caregiver $caregiver){

        return response()->json([
            'caregiver'=>$caregiver,
            'address'=>$caregiver->getAddress(),
            'phone'=>$caregiver->getPhoneNumber()->number
        ]);

    }
}
