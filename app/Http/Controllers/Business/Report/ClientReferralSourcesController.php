<?php


namespace App\Http\Controllers\Business\Report;

use App\Http\Controllers\Business\BaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Shifts\ShiftStatusManager;


class ClientReferralSourcesController extends BaseController
{
    public function index(Request $request)
    {

        if ($request->expectsJson()) {
            $query = $this->businessChain()->referralSources()
                ->forType('client')
                ->withCount('clients', 'prospects')
                ->with([
                    'clients',
                    'clients.shifts.business',
                    'clients.shifts.client',
                    'clients.shifts.caregiver',
                    'clients.shifts.shiftFlags',
                    'clients.shifts.statusHistory',
                    'clients.shifts.costHistory',
                    'clients.shifts.service',
                    'clients.shifts.services',
                    'clients.shifts.services.service',
                    'clients.shifts.client.primaryPayer',
                    'clients.shifts.client.primaryPayer.payer',
                    'clients.shifts.client.primaryPayer.client',
                    'clients.shifts.client.primaryPayer.client.business',
                    'clients.shifts.client.primaryPayer.paymentMethod',
                ])
                ->whereHas('clients.shifts', function ($q) {
                    $q->whereNotIn('status', ShiftStatusManager::getPendingStatuses());
                })
                ->ordered();

            if ($request->referral_source) {
                $query->where('id', $request->referral_source);
            }

            if ($request->start_date && $request->end_date) {
                $query->whereBetween('created_at', [
                        new Carbon($request->start_date . "00:00:00"),
                        new Carbon($request->end_date . "23:59:59")]
                );
            }

            $items = $query->get();
            $results = [];

            foreach ($items as $item) {
                $results[] = [
                    "id" => $item->id,
                    "business_id" => $item->business_id,
                    "organization" => $item->organization,
                    "contact_name" => $item->contact_name,
                    "phone" => $item->phone,
                    "created_at" => Carbon::parse($item->created_at)->format('d/m/Y'),
                    "clients_count" => $item->clients_count,
                    "prospects_count" => $item->prospects_count,
                    "shift_total" => number_format(($item->clients->map(function($item) {
                        return $item->shifts->map(function($shift) {
                            return $shift->costs()->getTotalCost();
                        })->sum();
                    }))->sum(), 2)
                ];
            }

            return response()->json(collect($results));
        }

        return view('business.reports.referral_sources', ['type' => 'client']);


    }
}