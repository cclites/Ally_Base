<?php

namespace App\Http\Controllers\Admin;

use App\Business;
use App\Imports\ImportManager;
use App\Responses\CreatedResponse;
use App\Shift;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShiftImportController extends Controller
{
    public function index()
    {
        return view('admin.import.shifts');
    }

    public function process(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'provider' => 'required|string',
            'file' => 'required|file',
        ]);

        $business = Business::findOrFail($request->business_id);
        $file = $request->file('file')->getPathname();

        $import = ImportManager::open($request->provider, $business, $file);
        return $import->handle();
    }

    public function store(Request $request)
    {
        $request->validate([
            'shifts.*.business_id' => 'required|exists:businesses,id',
            'shifts.*.caregiver_id' => 'required|exists:caregivers,id',
            'shifts.*.client_id' => 'required|exists:clients,id',
            'shifts.*.checked_in_time' => 'required|date',
            'shifts.*.checked_out_time' => 'required|date',
            'shifts.*.caregiver_rate' => 'numeric',
            'shifts.*.provider_fee' => 'numeric',
            'shifts.*.mileage' => 'numeric',
            'shifts.*.other_expenses' => 'numeric'
        ]);

        $shifts = collect();
        foreach($request->shifts as $data) {
            $shift = new Shift($data);
            $shift->status = Shift::WAITING_FOR_AUTHORIZATION;
            if ($shift->save()) {
                $shifts->push($shift);
            }
        }

        return new CreatedResponse("{$shifts->count()} shifts created.");
    }
}
