<?php

namespace App\Http\Controllers\Business\Report;

use App\Caregiver;
use App\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CaregiverStatsController extends Controller
{
    public function index()
    {
        return view('business.reports.caregiver_stats');
    }

    public function reportData(Request $request)
    {
        $dates = [$request->start_date, $request->end_date];
        // get caregivers with shifts inside the requested time period
        $caregivers = Caregiver::forRequestedBusinesses()
            ->whereHas('shifts', function ($query) use ($dates) {
                $query->betweenDates($dates[0], $dates[1])
                    ->whereConfirmed();
            })
            ->with([
                'shifts' => function ($query) use ($dates) {
                    $query->betweenDates($dates[0], $dates[1])
                        ->whereConfirmed();
                },
                'shifts.activities'
            ])
            ->when($request->filled('caregiver_status'), function ($query) use ($request) {
                $active = $request->caregiver_status == 'active' ? true : false;
                $query->whereHas('user', function ($query) use ($active) {
                    $query->where('active', $active);
                });
            })
            ->get();
        $caregiverTopActivities = $caregivers->map(function (Caregiver $caregiver) {
            $topActivities = $caregiver->shifts
                ->pluck('activities')
                ->filter(function ($item) {
                    return count($item);
                })
                ->flatten()
                ->groupBy('id')
                ->map(function ($item, $key) {
                    return [
                        'id' => $key,
                        'name' => collect($item)->first()->name,
                        'count' => collect($item)->count()
                    ];
                })
                ->sortByDesc('count')
                ->values()
                ->take(3)
                ->toArray();

            return [
                'id' => $caregiver->id,
                'name' => $caregiver->name,
                'activity_1' => isset($topActivities[0]) ? $topActivities[0] : null,
                'activity_2' => isset($topActivities[1]) ? $topActivities[1] : null,
                'activity_3' => isset($topActivities[2]) ? $topActivities[2] : null,
            ];
        });

        $averageAge = round($caregivers->map(function ($item) {
            return $item->user->getAge();
        })
            ->filter()
            ->average());

        $totalCaregiverHours = $caregivers->reduce(function ($carry, $item) {
            return $carry + $item->shifts->reduce(function ($carry, $item) {
                    return $carry + $item->hours;
                });
        });

        $genders = [
            'female' => $caregivers->whereIn('gender', ['f', 'F'])->count(),
            'male' => $caregivers->whereIn('gender', ['m', 'M'])->count()
        ];

        $genders['unassigned'] = $caregivers->count() - ($genders['male'] + $genders['female']);
        $totalCaregivers = $caregivers->count();

        return response()->json(compact(
                'totalCaregiverHours',
                'totalCaregivers',
                'caregiverTopActivities',
                'genders',
                'averageAge',
                'activities')
        );
    }
}
