<?php

namespace App\Http\Controllers\Business;

use App\Activity;
use App\Responses\CreatedResponse;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Rules\ValidActivityCode;
use Illuminate\Http\Request;

class QuickbooksController extends BaseController
{
    public function index(Request $request)
    {
        $clients = $this->getClients($request);
        $caregivers = $this->getCaragivers($request);


        return view('business.quickbooks.index', compact('clients', 'caregivers'));
    }

    private function getClients($request) {
        $clients = $this->business()->clients()->with(['user', 'addresses', 'phoneNumbers'])
            ->when($request->filled('client_type'), function($query) use ($request) {
                $query->where('client_type', $request->input('client_type'));
            })
            ->when($request->filled('active') || $request->expectsJson(), function($query) use ($request) {
                $query->where('active', $request->input('active', 1));
            })
            ->orderByName()
            ->get()
            ->map(function ($client) {
                if ($client->addresses->count() == 1) {
                    $client->county = $client->addresses->first()->county;
                } elseif ($client->addresses()->count() > 1) {
                    $client->county = optional($client->addresses->where('type', 'evv')->first())->county;
                }
                return $client;
            })
            ->values();

        if ($request->expectsJson()) {
            return $clients;
        }

        return $clients;
    }

    private function getCaragivers($request) {
        $caregivers = $this->business()->caregivers()
            ->when($request->filled('active') || $request->expectsJson(), function($query) use ($request) {
                $query->where('active', $request->input('active', 1));
            })
            ->orderByName()
            ->with(['user', 'addresses', 'phoneNumbers'])
            ->get();

        if ($request->expectsJson()) {
            return $caregivers;
        }

        return $caregivers;
    }
}
