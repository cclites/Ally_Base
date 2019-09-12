<?php


namespace App\Http\Controllers\Business\Report;

use App\Http\Controllers\Business\BaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Shifts\ShiftStatusManager;

class CaregiverReferralSourcesController extends BaseController
{
    public function index(Request $request){

        if ($request->expectsJson()) {

            $query = $this->businessChain()->referralSources()
                ->forType('caregiver')
                ->withCount('caregivers')
                ->with([
                    'caregivers',
                    'caregivers.shifts.business',
                    'caregivers.shifts.client',
                    'caregivers.shifts.caregiver',
                    'caregivers.shifts.shiftFlags',
                    'caregivers.shifts.statusHistory',
                    'caregivers.shifts.costHistory',
                    'caregivers.shifts.service',
                    'caregivers.shifts.services',
                    'caregivers.shifts.services.service',
                    'caregivers.shifts.client.primaryPayer',
                    'caregivers.shifts.client.primaryPayer.payer',
                    'caregivers.shifts.client.primaryPayer.client',
                    'caregivers.shifts.client.primaryPayer.client.business',
                    'caregivers.shifts.client.primaryPayer.paymentMethod',
                ])
                ->whereHas('caregivers.shifts', function ($q) {
                   $q->whereNotIn('status', ShiftStatusManager::getPendingStatuses());
                })
                ->ordered();

            if($request->referral_source) {
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

            foreach($items as $item) {
                $results[] = [
                    "id" => $item->id,
                    "business_id" => $item->business_id,
                    "organization" => $item->organization,
                    "contact_name" => $item->contact_name,
                    "phone" => $item->phone,
                    "created_at" => Carbon::parse($item->created_at)->format('d/m/Y'),
                    "caregivers_count" => $item->caregivers_count,
                    "shift_total" => ($item->caregivers->map(function($item) {
                        return $item->shifts->map(function($shift) {
                            return number_format($shift->costs()->getTotalCost(), 2);
                        })->sum();
                    }))->sum()
                ];
            }

            return response()->json(collect($results));
        }

        return view('business.reports.referral_sources', ['type' => 'caregiver']);
    }
}