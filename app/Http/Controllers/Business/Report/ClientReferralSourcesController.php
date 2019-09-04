<?php


namespace App\Http\Controllers\Business\Report;

use App\Http\Controllers\Business\BaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;


class ClientReferralSourcesController extends BaseController
{
    public function index(Request $request)
    {

        //$type = $request->type;
        if ($request->expectsJson())
        {
            $type = 'client';

            $referralsources = $this->businessChain()
                ->referralSources()
                ->forType($type)
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
                ->whereBetween('created_at', [new Carbon($request->start_date), new Carbon($request->start_date)])
                ->ordered()
                ->get();
        }

        return view('business.reports.referral_sources', ['type' => 'client']);


        /*
        if ($request->expectsJson()) {
            $results = [];

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
                });

            if ($request->referral_source) {
                $query->where('id', $request->referral_source);
            }

            if ($request->start_date && $request->end_date) {
                $query->where('created_at','>', (new Carbon($request->start_date)));
                $query->where('created_at','<', (new Carbon($request->end_date)));
            }

            foreach ($query->get() as $item) {
                $results[] = [
                    "id" => $item->id,
                    "business_id" => $item->business_id,
                    "organization" => $item->organization,
                    "contact_name" => $item->contact_name,
                    "phone" => $item->phone,
                    "created_at" => Carbon::parse($item->created_at)->format('d/m/Y'),
                    "clients_count" => $item->clients_count,
                    "prospects_count" => $item->prospects_count,
                    "shift_total" => ($item->clients->map(function($item) {
                        return $item->shifts->map(function($shift) {
                            return number_format($shift->costs()->getTotalCost(), 2);
                        })->sum();
                    }))->sum()
                ];
            }

            return response()->json(collect($results));
        }

        return view('business.reports.referral_sources', ['type' => 'client']);
        */
    }
}