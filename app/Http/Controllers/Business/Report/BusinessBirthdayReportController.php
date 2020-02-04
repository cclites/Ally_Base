<?php

namespace App\Http\Controllers\Business\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Reports\BirthdayReport;
use App\Caregiver;
use App\Client;

class BusinessBirthdayReportController extends Controller {
    public function index(Request $request) {
        $report = new BirthdayReport($request->type);
        $report->includeContactInfo();

        $userId = $request->selectedId;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $clientType = $request->selectedClients;

        if ($userId != 'All' && $userId) {
            $report->filterByClientId($userId);
        }

        if($clientType != 'All'  && $clientType) {
            $report->filterByClientType($clientType);
        }

        if($request->filterDates) {
            $report->filterByDateRange($startDate, $endDate);
        }

        if ($request->filled('json')) {
            return response()->json($report->rows());
        }

        $type = $request->type == 'clients' ? 'clients' : 'caregivers';
        $type = ucfirst($type);

        $clients =  Client::forRequestedBusinesses();

        $clientTypes = $clients->distinct('client_type')->get(['client_type'])->map(function ($client) {
            return $client->client_type;
        })->values();

        $clientList = $clients->select('id')->get()->sortBy('name')->values();

        $caregiverList = Caregiver::forRequestedBusinesses()->select('id')->get()->sortBy('name')->values();

        return view('business.reports.user_birthday', compact('type', 'clientTypes', 'clientList', 'caregiverList'));
    }
}
