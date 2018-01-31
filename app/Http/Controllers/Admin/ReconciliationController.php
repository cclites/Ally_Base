<?php

namespace App\Http\Controllers\Admin;

use App\Business;
use App\Caregiver;
use App\Client;
use App\Reports\AdminReconciliationReport;
use App\Http\Controllers\Controller;

class ReconciliationController extends Controller
{
    public function index()
    {
        return view('admin.reports.reconciliation');
    }

    public function business(Business $business)
    {
        $report = new AdminReconciliationReport($business);
        return $report->rows();
    }

    public function caregiver(Caregiver $caregiver)
    {
        $report = new AdminReconciliationReport($caregiver);
        return $report->rows();
    }

    public function client(Client $client)
    {
        $report = new AdminReconciliationReport($client);
        return $report->rows();
    }
}
