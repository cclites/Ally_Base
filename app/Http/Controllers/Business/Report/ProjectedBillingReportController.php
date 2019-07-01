<?php

namespace App\Http\Controllers\Business\Report;

use App\Caregiver;
use App\Client;
use App\Schedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Controllers\Controller;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Http\Request;

class ProjectedBillingReportController extends Controller
{
    /**
     * Get the report.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if ($request->filled('json')) {
            return response()->json($this->getData());
        }

        return view_component('projected-billing-report', 'Projected Billing Report', [], [
            'Home' => route('home'),
            'Reports' => route('business.reports.index')
        ]);
    }

    /**
     * Get the filter options for the report.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterOptions()
    {
        $clients = Client::with('user')
            ->forRequestedBusinesses()
            ->active()
            ->select('id', 'client_type')
            ->get()
            ->map(function (Client $client) {
                return [
                    'id' => $client->id,
                    'name' => $client->nameLastFirst,
                    'client_type' => $client->client_type,
                ];
            })
            ->sortBy('name')
            ->values();

        $caregivers = Caregiver::with('user')
            ->forRequestedBusinesses()
            ->active()
            ->select('id')
            ->get()
            ->map(function (Caregiver $caregiver) {
                return [
                    'id' => $caregiver->id,
                    'name' => $caregiver->nameLastFirst,
                ];
            })
            ->sortBy('name')
            ->values();

        $clientTypes = $clients->pluck('client_type')
            ->unique()
            ->map(function ($item) {
                return [
                    'id' => $item,
                    'name' => title_case(str_replace('_', ' ', $item)),
                ];
            })
            ->sortBy('name')
            ->values();

        return response()->json(compact('clients', 'clientTypes', 'caregivers'));
    }

    public function print()
    {
        $data = $this->getData();
        $pdf = PDF::loadView('business.reports.print.projected_billing', $data);
        return $pdf->download('projected_billing.pdf');
    }

    protected function getData(): array
    {
        dd('no');
        $dates = (object)request()->dates;
        $start = Carbon::parse($dates->start);
        $end = Carbon::parse($dates->end);
        $schedules = Schedule::forRequestedBusinesses()
            ->has('client.business')
            ->with('client')
            ->whereHas('caregiver')
            ->when(request()->filled('client'), function ($query) {
                $query->where('client_id', request('client'));
            })
            ->when(request()->filled('caregiver'), function ($query) {
                $query->where('caregiver_id', request('caregiver'));
            })
            ->when(request()->filled('clientType'), function ($query) {
                $query->whereHas('client', function ($query) {
                    $query->where('client_type', request('clientType'));
                });
            })
            ->whereBetween('starts_at', [$start, $end])
            ->get();
        $clientTypeStats = $this->getClientTypeStats($schedules);
        $clientStats = $this->getClientStats($schedules);

        $stats = collect([
            'total_hours' => $schedules->sum('duration') / 60,
            'total_clients' => $schedules->pluck('client_id')->unique()->count(),
            'projected_total' => $schedules->reduce(function ($carry, $item) {
                return $carry + ($item->duration / 60) * $item->getCaregiverRate();
            })
        ]);
        return compact('stats', 'clientStats', 'clientTypeStats', 'dates');
    }

    protected function getClientStats(Collection $schedules): \Illuminate\Support\Collection
    {
        $clients = $schedules->groupBy('client_id');
        $clientStats = collect([]);
        foreach ($clients as $key => $value) {
            $clientStats->push([
                'name' => data_get($value->first(), 'client.name'),
                'hours' => $value->sum('duration') / 60,
                'projected_billing' => $value->reduce(function ($carry, $item) {
                    return $carry + ($item->duration / 60) * $item->caregiver_rate;
                })
            ]);
        }
        return $clientStats;
    }

    protected function getClientTypeStats(Collection $schedules): \Illuminate\Support\Collection
    {
        $clientTypes = $schedules->pluck('client.client_type')->unique()->toArray();
        $clientTypeStats = collect([]);
        foreach ($clientTypes as $type) {
            $typeSchedules = $schedules->where('client.client_type', $type);
            $clientTypeStats->push([
                'name' => $type,
                'projected_billing' => $typeSchedules->reduce(function ($carry, $item) {
                    return $carry + ($item->duration / 60) * $item->caregiver_rate;
                })
            ]);
        }
        return $clientTypeStats;
    }
}
