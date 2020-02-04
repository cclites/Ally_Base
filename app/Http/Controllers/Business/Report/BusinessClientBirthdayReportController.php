<?php

namespace App\Http\Controllers\Business\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Reports\ClientBirthdayReport;
use App\Caregiver;
use App\Client;

class BusinessClientBirthdayReportController extends Controller {
    public function index(Request $request, ClientBirthdayReport $report) {
        $report->includeContactInfo();

        $clientId = $request->selectedId;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if ($clientId != 'All' && $clientId) {
            $report->filterByClientId($clientId);
        }

        if($clientType = $request->client_type) {
            $report->filterByClientType($clientType);
        }

        if($request->filterDates) {
            $report->filterByDateRange($startDate, $endDate);

           // dd($report->query()->toSql(), $report->query()->getBindings());
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
