<?php

namespace App\Http\Controllers\Business\Report;

use App\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class ClientStatsController extends Controller
{
    public function index()
    {
        $clientTypes = Client::distinct()->select('client_type')
            ->pluck('client_type')
            ->filter()
            ->map(function ($item) {
                return [
                    'name' => Str::title(str_replace('_', ' ', $item)),
                    'id' => $item
                ];
            });

        return view('business.reports.client_stats', compact('clientTypes'));
    }

    public function reportData(Request $request)
    {
        $dates = [$request->start_date, $request->end_date];
        // get clients with shifts inside the requested time period
        $clients = Client::forRequestedBusinesses()
            ->whereHas('shifts', function ($query) use ($dates) {
                $query->betweenDates($dates[0], $dates[1])
                    ->whereConfirmed();
            })
            ->with(['shifts' => function ($query) use ($dates) {
                $query->betweenDates($dates[0], $dates[1])
                    ->whereConfirmed();
            },
                'shifts.activities'])
            ->when($request->filled('client_status'), function ($query) use ($request) {
                $active = $request->client_status == 'active' ? true : false;
                $query->whereHas('user', function ($query) use ($active) {
                    $query->where('active', $active);
                });
            })
            ->when($request->filled('client_type'), function ($query) use ($request) {
                $query->where('client_type', $request->client_type);
            })
            ->get();
        $activities = $clients->pluck('shifts')
            ->flatten()
            ->pluck('activities')
            ->flatten()
            ->groupBy('name')
            ->map(function ($item, $key) {
                return [
                    'name' => $key,
                    'count' => $item->count()
                ];
            })
            ->values();

        $averageAge = round($clients->map(function ($item) {
            return $item->user->getAge();
        })
            ->filter()
            ->average());

        $totalClientHours = $clients->reduce(function ($carry, $item) {
            return $carry + $item->shifts->reduce(function ($carry, $item) {
                    return $carry + $item->hours;
                });
        });

        $genders = [
            'female' => $clients->whereIn('gender', ['f', 'F'])->count(),
            'male' => $clients->whereIn('gender', ['m', 'M'])->count()
        ];

        $genders['unassigned'] = $clients->count() - ($genders['male'] + $genders['female']);

        $totalClientsServiced = $clients->count();
        return response()->json(compact(
                'totalClientsServiced',
                'totalClientHours',
                'genders',
                'averageAge',
                'activities')
        );
    }
}
