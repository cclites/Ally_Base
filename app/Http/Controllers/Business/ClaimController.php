<?php
namespace App\Http\Controllers\Business;

use App\Client;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

class ClaimController extends BaseController
{
    public function report()
    {
        return view('business.reports.claims');
    }

    public function data(Request $request)
    {
        $request->validate([
            'client_id' => 'required|int',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'export_type' => 'required|string'
        ]);

        $client = Client::with([
            'addresses',
            'shifts' => function ($query) use ($request) {
                $query->whereBetween('checked_in_time', [Carbon::parse($request->start_date), Carbon::parse($request->end_date)])
                    ->whereNotNull('checked_out_time');
            }
        ])
            ->find($request->client_id);

        $summary = [];
        foreach ($client->shifts as $shift) {
            $summary[] = [
                'date' => $shift->checked_in_time->format('Y-m-d'),
                'total' => (float) $shift->shift_total = $shift->costs()->getTotalCost(),
                'hours' => $shift->duration,
                'hourly_total' => $shift->costs()->getTotalHourlyCost(),
            ];
        }

        return response()->json(compact('client', 'summary'));
    }

    public function print(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'export_type' => 'nullable|string',
            'report_type' => 'string|in:full,notes',
        ]);

        $client = Client::findOrFail($request->client_id);
        if (!$this->businessHasClient($client)) {
            abort(403);
        }

        $start_date = Carbon::parse($request->start_date, $this->business()->timezone);
        $end_date = Carbon::parse($request->end_date, $this->business()->timezone);

        // Shifts
        $shifts = $client->shifts()->whereBetween('checked_in_time', [
            $start_date->copy()->setTimezone('UTC')->toDateTimeString(),
            $end_date->copy()->setTimezone('UTC')->toDateTimeString(),
        ])
            ->with(['activities', 'issues', 'client', 'client.evvAddress', 'caregiver'])
            ->orderBy('checked_in_time')
            ->get();

        // Additional view data
        $totalAmount = $shifts->reduce(function($carry, Shift $shift) {
            return bcadd($carry, $shift->costs()->getTotalCost(), 2);
        });
        $claimNumber = $client->ltci_claim;
        $policyNumber = $client->ltci_policy;
        $business = $this->business();
        $timezone = $business->timezone;
        $report_type = $request->report_type == 'notes' ? 'notes' : 'full';
        
        // ============= //
        $viewData = compact('client', 'business', 'timezone', 'claimNumber', 'policyNumber', 'shifts', 'totalAmount', 'start_date', 'end_date', 'report_type');

        switch ($request->export_type) {
            case 'pdf':
                $pdf = PDF::loadView('business.reports.print.insurance_claim', $viewData);
                return $pdf->download(str_slug($client->name.' Claim').'.pdf');
            default:
                return view('business.reports.print.insurance_claim', $viewData);
        }
    }
}