<?php

namespace App\Http\Controllers\Business\Report;

use App\Caregiver;
use App\Client;
use App\Schedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;


class ProjectedBillingReportController extends Controller
{
    public function index()
    {
        $clientOptions = Client::forRequestedBusinesses()
            ->select('id', 'client_type')
            ->get()
            ->sortBy('name')
            ->values();

        $caregiverOptions = Caregiver::forRequestedBusinesses()
            ->select('id')
            ->get()
            ->sortBy('name')
            ->values();

        $clientTypeOptions = $clientOptions->pluck('client_type')
            ->unique()
            ->map(function ($item) {
                return [
                    'id' => $item,
                    'name' => title_case(str_replace('_', ' ', $item)),
                ];
            })
            ->values();

        return view('business.reports.projected_billing', compact('clientOptions', 'caregiverOptions', 'clientTypeOptions'));
    }

    public function reportData()
    {
        $data = $this->getData();
        return response()->json($data);
    }

    public function print()
    {
        $data = $this->getData();
        $pdf = PDF::loadView('business.reports.print.projected_billing', $data);
        return $pdf->download('projected_billing.pdf');
    }

    protected function getData(): array
    {
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
