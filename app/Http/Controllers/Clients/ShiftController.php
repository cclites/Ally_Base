<?php
namespace App\Http\Controllers\Clients;

use App\Http\Controllers\Controller;
use App\Responses\SuccessResponse;
use App\Shift;
use App\Signature;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index($week = null)
    {
        $week_start_date = Carbon::now();
        $week_end_date = Carbon::now();
        if (is_null($week)) {
            $week_start_date->setISODate($week_start_date->year, $week_start_date->weekOfYear);
            $week_end_date->setISODate($week_end_date->year, $week_end_date->weekOfYear, 7);
        } else {
            $week_start_date->setISODate($week_start_date->year, $week);
            $week_end_date->setISODate($week_end_date->year, $week, 7);
        }

        $shifts = Shift::with('caregiver')
            ->where('client_id', auth()->id())
            ->whereBetween('checked_in_time', [$week_start_date->format('Y-m-d'), $week_end_date->format('Y-m-d')])
            ->orderBy('checked_in_time')
            ->get();

        $shifts = $shifts->map(function(Shift $shift) {
            $shift->total = $shift->duration() * $shift->caregiver_rate + $shift->provider_fee + $shift->other_expenses;
            return $shift;
        });

        $shifts_verified = false;

        if ($shifts->count()) {
            $shifts_verified = !is_null($shifts->first()->signature) && !is_null($shifts->last()->signature);
        }

        if (request()->expectsJson()) {
            $week_start_date = $week_start_date->format('Y-m-d H:i:s');
            $week_end_date = $week_end_date->format('Y-m-d H:i:s');
            return response()->json(compact('shifts', 'week_start_date', 'week_end_date', 'shifts_verified'));
        }

        return view('clients.shift_history', compact('shifts', 'week_start_date', 'week_end_date', 'shifts_verified'));
    }

    public function show(Request $request, Shift $shift)
    {
        if ($shift->client_id != auth()->id()) {
            return new ErrorResponse(403, 'You do not have access to this shift.');
        }

        // Load needed relationships
        $shift->load(['activities', 'issues', 'schedule', 'client', 'caregiver', 'signature', 'statusHistory', 'goals', 'questions']);
        $shift->append(['ally_pct', 'charged_at', 'confirmed_at']);

        // Load shift data into array before loading client info
        $data = $shift->toArray();

        // Calculate distances
        $checked_in_distance = null;
        $checked_out_distance = null;
        if ($address = $shift->client->evvAddress) {
            if ($shift->checked_in_latitude || $shift->checked_in_longitude) {
                $checked_in_distance = $address->distanceTo($shift->checked_in_latitude, $shift->checked_in_longitude);
            }
            if ($shift->checked_out_latitude || $shift->checked_out_longitude) {
                $checked_out_distance = $address->distanceTo($shift->checked_out_latitude, $shift->checked_out_longitude);
            }
        }

        $data += [
            'checked_in_distance' => $checked_in_distance,
            'checked_out_distance' => $checked_out_distance,
            'client_name' => $shift->client->name(),
            'caregiver_name' => $shift->caregiver->name(),
            'address' => optional($shift->address)->only(['latitude', 'longitude']),
        ];

        return response()->json($data);
    }

    public function approveWeek(Request $request)
    {
        $request->validate([
            'week' => 'required|integer',
            'name' => 'required|string|min:2',
        ]);

        $week = $request->week;
        $name = $request->name;

        $week_start_date = Carbon::now();
        $week_end_date = Carbon::now();
        $week_start_date->setISODate($week_start_date->year, $week);
        $week_end_date->setISODate($week_end_date->year, $week, 7);
        $week_start_date = $week_start_date->format('Y-m-d');
        $week_end_date = $week_end_date->format('Y-m-d');

        $shifts = Shift::where('client_id', auth()->id())
            ->whereBetween('checked_in_time', [$week_start_date, $week_end_date])
            ->get();

        foreach($shifts as $shift) {
            if (!$shift->signature) {
                $shift->signature()->save(new Signature(['content' => $name]));
            }
        }

        return new SuccessResponse('Shifts approved.');
    }
}
